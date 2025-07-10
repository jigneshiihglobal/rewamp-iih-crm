<?php

namespace App\Services;


use App\Models\Invoice;
use App\Models\InvoiceItem;
use QuickBooksOnline\API\DataService\DataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use QuickBooksOnline\API\Facades\Invoice as QBOInvoice;

class QuickBooksInvoiceService
{
    public static function syncInvoiceToQuickBooks(Invoice $invoice)
    {
        try {
            $userId = auth()->id();
            $quickbooksToken = DB::table('quickbooks_tokens')->where('user_id', $userId)->first();

            if (!$quickbooksToken) {
                Log::error("QuickBooksInvoiceService: No QuickBooks token found", ['user_id' => $userId]);
                return;
            }

            $dataService = DataService::Configure([
                'auth_mode'       => 'oauth2',
                'ClientID'        => config('services.quickbooks.client_id'),
                'ClientSecret'    => config('services.quickbooks.client_secret'),
                'accessTokenKey'  => $quickbooksToken->access_token,
                'refreshTokenKey' => $quickbooksToken->refresh_token,
                'QBORealmID'      => $quickbooksToken->realm_id,
                'RedirectURI'     => config('services.quickbooks.redirect_uri'),
                'baseUrl'         => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production',
            ]);

            $dataService->throwExceptionOnError(true);
            $dataService->setLogLocation(storage_path('logs/quickbooks'));

            $client = $invoice->client;
            if (!$client || !$client->qb_customer_id) {
                Log::warning("QuickBooksInvoiceService: Client missing QuickBooks customer ID", ['client_id' => $client->id]);
                return;
            }

            $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                Log::warning("QuickBooksInvoiceService: Invoice has no line items", ['invoice_id' => $invoice->id]);
                return;
            }

            // Prepare line items
            $lineItems = [];
            foreach ($invoiceItems as $item) {
                $lineItems[] = [
                    "Description" => $item->description ?? 'Item',
                    "Amount" => round($item->total_price, 2),
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "Qty" => $item->quantity ?? 1,
                        "UnitPrice" => round($item->total_price, 2),
                        "TaxCodeRef" => [
                            "value" => ($item->tax_rate > 0) ? 'TAX' : 'NON',
                        ],
                    ]
                ];
            }

            // Prepare shared invoice data
            $qbInvoiceData = [
                "CustomerRef" => [
                    "value" => $client->qb_customer_id,
                ],
                "Line" => $lineItems,
                "TxnDate" => $invoice->invoice_date->format('Y-m-d'),
                "DueDate" => $invoice->due_date->format('Y-m-d'),
                "PrivateNote" => 'Invoice #' . $invoice->invoice_number,
            ];

            // Step: Decide between update or create
            $isUpdate = false;
            $existingInvoice = null;

            if ($invoice->qb_invoice_id) {
                $existingInvoice = $dataService->FindById('Invoice', $invoice->qb_invoice_id);

                if ($existingInvoice) {
                    $isUpdate = true;

                    // Wrap and merge data properly using SDK helper
                    $updatedInvoice = QBOInvoice::update($existingInvoice, $qbInvoiceData);
                    $updatedInvoice = $dataService->Update($updatedInvoice);

                    if (!$updatedInvoice) {
                        $error = $dataService->getLastError();
                        Log::error("QuickBooksInvoiceService: Error updating invoice", [
                            'status_code' => $error->getHttpStatusCode(),
                            'helper_msg' => $error->getOAuthHelperError(),
                            'response_body' => $error->getResponseBody(),
                        ]);
                        return;
                    }

                    Log::info("QuickBooksInvoiceService: Invoice updated successfully", [
                        'QB_Invoice_ID' => $updatedInvoice->Id
                    ]);
                } else {
                    // Not found in QB, fall back to create
                    Log::warning("QuickBooksInvoiceService: QB invoice ID not found in QuickBooks. Will create new.", [
                        'qb_invoice_id' => $invoice->qb_invoice_id
                    ]);
                }
            }

            if (!$isUpdate) {
                // Create new invoice
                $qboInvoice = QBOInvoice::create($qbInvoiceData);
                $createdInvoice = $dataService->Add($qboInvoice);

                if (!$createdInvoice) {
                    $error = $dataService->getLastError();
                    Log::error("QuickBooksInvoiceService: Error creating invoice", [
                        'status_code' => $error->getHttpStatusCode(),
                        'helper_msg' => $error->getOAuthHelperError(),
                        'response_body' => $error->getResponseBody(),
                    ]);
                    return;
                }

                Log::info("QuickBooksInvoiceService: Invoice created successfully", [
                    'QB_Invoice_ID' => $createdInvoice->Id
                ]);

                // Save QB invoice ID locally
                $invoice->update(['qb_invoice_id' => $createdInvoice->Id]);
            }

        } catch (\Exception $e) {
            Log::error("QuickBooksInvoiceService: Exception during invoice sync", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

}