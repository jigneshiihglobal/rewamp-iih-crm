<?php

namespace App\Services;

use App\Enums\InvoiceType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Helpers\FileHelper;
use App\Mail\PaymentReceiptMail;
use App\Mail\PaymentReceivedMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * This function generates a new
     * invoice number which doesn't
     * already exists in database
     *
     * @author Krunal Shrimali
     * @return string Newly generated invoice number
     */
    public function new_number()
    {
        $workspaceId = Auth::user()->workspace_id;

        // Temp Code for shalin
        if ($workspaceId == 2) {
            // Use max ID instead of invoice_number
            $invoice = Invoice::select('invoice_number')->withTrashed()
                ->where('type', InvoiceType::INVOICE)
                ->whereHas('client', function ($q) use ($workspaceId) {
                    $q->where('workspace_id', $workspaceId);
                })
                ->orderByDesc('id')
                ->first(); // <-- max id
            $invoice_number = $invoice->invoice_number;
        } else {
            // Use max invoice_number as usual
            $invoice_number = Invoice::withTrashed()->where('type', InvoiceType::INVOICE)->whereHas('client', function ($q) {
                $q->where('workspace_id', Auth::user()->workspace_id);
            })->max('invoice_number'); // <-- max invoice_number
        }
    
        /*$invoice_number = Invoice::withTrashed()->where('type', InvoiceType::INVOICE)->whereHas('client', function ($q) {
            $q->where('workspace_id', Auth::user()->workspace_id);
        })->max('invoice_number');*/
        $start_from = (int) config('custom.invoice_number_starts_from');
        $prefix = config('custom.invoice_prefix');

        if (!$invoice_number || substr($invoice_number, 0, strlen($prefix)) !== $prefix) {
            return $prefix . $start_from;
        }

        $number_part = (int) str_replace($prefix, '', $invoice_number);

        if (!$number_part) {
            return $prefix . $start_from;
        }

        return
            $prefix .
            ($number_part < (int) $start_from
                ? $start_from
                : ++$number_part
            );
    }

    /**
     * This function generates an array of
     * new invoice number which doesn't
     * already exists in database
     *
     * @author Krunal Shrimali
     * @param int $count How many numbers to generate
     * @return array Newly generated invoice numbers
     */
    public function new_numbers(int $client_id): string
    {
        $client = Client::find($client_id);
        if (!$client) {
            throw new \Exception("Client not found.");
        }

        $workspaceId = $client->workspace_id;

        // Temp Code for shalin
        if ($workspaceId == 2) {
            // Use max ID instead of invoice_number
            $invoice = Invoice::select('invoice_number')->withTrashed()
                ->where('type', InvoiceType::INVOICE)
                ->whereHas('client', function ($q) use ($workspaceId) {
                    $q->where('workspace_id', $workspaceId);
                })
                ->orderByDesc('id')
                ->first(); // <-- max id
            $invoice_number = $invoice->invoice_number;
        } else {
            // Use max invoice_number as usual
            $invoice_number = Invoice::withTrashed()->where('type', InvoiceType::INVOICE)->whereHas('client', function ($q) use($workspaceId) {
                $q->where('workspace_id', $workspaceId);
            })->max('invoice_number'); // <-- max invoice_number
        }


        /*$invoice_number = Invoice::withTrashed()->where('type', InvoiceType::INVOICE)->whereHas('client', function ($q) use($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        })->max('invoice_number');*/
        $start_from = (int) config('custom.invoice_number_starts_from');
        $prefix = config('custom.invoice_prefix');

        if (!$invoice_number || substr($invoice_number, 0, strlen($prefix)) !== $prefix) {
            return $prefix . $start_from;
        }

        $number_part = (int) str_replace($prefix, '', $invoice_number);

        if (!$number_part) {
            return $prefix . $start_from;
        }

        $new_invoice_number = $prefix .
            ($number_part < (int) $start_from
                ? $start_from
                : ++$number_part
        );

        return $new_invoice_number;
    }

    /**
     * Generate a file name with
     * extension for pdf file
     * from invoice number
     *
     * @author Krunal Shrimali
     * @param string $invoice_number
     * @return string File name with extension
     */
    public function pdf_name($invoice_number = "", $type = 'invoice', $workspace = 'iih-global'): string
    {
        switch ($type) {
            case 'payment_receipt':
                return 'Payment Receipt.pdf';
                break;
            case 'credit_note':
                return $invoice_number . '.pdf';
                break;

            default:
                $replace_prefix_with = $workspace === 'shalin-designs'
                    ? 'Shalin Designs Invoice-'
                    : 'IIH Global Invoice-';
                return str_replace(config('custom.invoice_prefix'), $replace_prefix_with, $invoice_number) . '.pdf';
                break;
        }
    }

    public function pdf(Invoice &$invoice, $type = 'invoice'): PDF
    {
        $invoice->refresh();
        $invoice->loadMissing('client.workspace');
        $workspace = $invoice->client->workspace->slug ?? 'iih-global';
        Log::info('PDF render: ' . $invoice->invoice_number);
        switch ($workspace) {
            case 'shalin-designs':
                switch ($type) {
                    case 'payment_receipt':
                        return FacadePdf::loadView('pdf.shalin-designs.invoices.one-off-receipt', compact('invoice'));
                    case 'credit_note':
                        return FacadePdf::loadView('pdf.shalin-designs.credit_notes.v1', compact('invoice'));
                    default:
                        return FacadePdf::loadView('pdf.shalin-designs.invoices.one-off', compact('invoice'));
                }
                break;

            default:
                switch ($type) {
                    case 'payment_receipt':
                        return FacadePdf::loadView('pdf.invoices.one-off-receipt', compact('invoice'));
                    case 'credit_note':
                        return FacadePdf::loadView('pdf.credit_notes.v1', compact('invoice'));
                    default:
                        return FacadePdf::loadView('pdf.invoices.one-off', compact('invoice'));
                }
                break;
        }
    }

    public function stream(Invoice &$invoice, $type = 'invoice')
    {
        switch ($type) {
            case 'payment_receipt':
                $filepath = $invoice->receipt_file_path;
                $filename = $invoice->receipt_file_name;
                $disk = $invoice->receipt_file_disk;
                break;
            case 'credit_note':
                $filepath = $invoice->invoice_file_path;
                $filename = $invoice->invoice_file_name;
                $disk = $invoice->invoice_file_disk;
                break;

            default:
                $filepath = $invoice->invoice_file_path;
                $filename = $invoice->invoice_file_name;
                $disk = $invoice->invoice_file_disk;
                break;
        }

        if (!$filepath || !Storage::disk($disk)->exists($filepath)) {
            $this->makeAndStorePDF($invoice, $type);
            switch ($type) {
                case 'payment_receipt':
                    $filepath = $invoice->receipt_file_path;
                    $filename = $invoice->receipt_file_name;
                    $disk = $invoice->receipt_file_disk;
                    break;
                case 'credit_note':
                    $filepath = $invoice->invoice_file_path;
                    $filename = $invoice->invoice_file_name;
                    $disk = $invoice->invoice_file_disk;
                    break;

                default:
                    $filepath = $invoice->invoice_file_path;
                    $filename = $invoice->invoice_file_name;
                    $disk = $invoice->invoice_file_disk;
                    break;
            }
        }

        $filepath = storage_path('app/' . $filepath);

        return response()->file($filepath, [
            "Content-type" => "application/pdf",
            "Content-Disposition" => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Send mail to the sales person assigned to customer
     * when a partial or full payment is received from
     * customer or when invoice is marked as payment
     *
     * @author Krunal Shrimali
     * @param string $invoice_number
     * @return string File name with extension
     */
    public function sendPaymentReceivedMail(Invoice $invoice, $mail_about = 'latest_payment')
    {
        try {
            $invoice = $invoice->fresh();
            $customerName = $amount = $currency = $salesPerson = $to = null;

            if ($mail_about == 'full_payment_received') {
                $invoice->loadMissing([
                    'sales_person',
                    'client',
                    'currency',
                ]);

                if (!$invoice->sales_person || !$invoice->sales_person->email) {
                    return;
                }

                $customerName = $invoice->client->name;
                $amount = number_format((float)$invoice->grand_total, 2, '.', '');
                $currency = $invoice->currency->symbol;
                $salesPerson =
                    $invoice->sales_person->full_name ??
                    $invoice->sales_person->first_name . " " . $invoice->sales_person->last_name
                    ?? "";
                $to = $invoice->sales_person;
            } else if ($mail_about == 'latest_payment') {

                if (!$invoice->payments()->count()) {
                    return;
                }
                $payment = $invoice
                    ->payments()
                    ->with([
                        'invoice',
                        'invoice.sales_person',
                        'invoice.client',
                        'invoice.currency',
                    ])
                    ->latest()
                    ->first();
                if (!$payment->invoice->sales_person || !$payment->invoice->sales_person->email) {
                    return;
                }
                $customerName = $payment->invoice->client->name;
                $amount = number_format((float)$payment->amount, 2, '.', '');
                $currency = $payment->invoice->currency->symbol;
                $salesPerson =
                    $payment->invoice->sales_person->full_name ??
                    $payment->invoice->sales_person->first_name . " " . $payment->invoice->sales_person->last_name
                    ?? "";
                $to = $payment->invoice->sales_person;
            }

            Mail::mailer(config('mail.accounts_mail_mailer', 'accounts_smtp'))
                ->to($to)
                ->send(
                    new PaymentReceivedMail($customerName, $amount, $currency, $salesPerson, Auth::user()->active_workspace->slug)
                );

            ActivityLogHelper::log(
                'invoices.payment_received.mail-sent-to-sales-person',
                "Mail sent (Payment received from {$customerName}) to {$salesPerson}",
                [
                    "amount" => $amount,
                    "currency" => $currency,
                ],
                request(),
                Auth::user(),
                $invoice
            );
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }

    /**
     * Generate a pdf and return
     * its raw contents
     * as string
     *
     * @author Krunal Shrimali
     * @param \App\Models\Invoice $invoice
     * @param array $domPDFOutputOptions Options to pass in dompdf output method if any
     * @param string $type type of generated pdf
     * @return string raw file contents
     */
    public function pdfOutput(Invoice $invoice, $domPDFOutputOptions = [], $type = "invoice"): string
    {
        return $this->pdf($invoice, $type)->output($domPDFOutputOptions);
    }

    /**
     * Send mail to the customer
     * containing the payment
     * receipt with paid date
     *
     * @author Krunal Shrimali
     * @param \App\Models\Invoice $invoice Invoice model instance
     * @return void
     */
    public function sendPaymentReceiptMail(Invoice $invoice,$sales_mail = false,$payment_method = null): void
    {
        try {
            $invoice = $invoice->fresh();
            $invoice->loadMissing('client', 'payments', 'invoice_items', 'currency', 'sales_person', 'client.workspace:id,slug');
            $customerName = $invoice->client->name ?? '';
            $invoice_number = $invoice->invoice_number ?? '';
            //$to = $invoice->client->email ?? '';
            $to = explode(',',$invoice->client->email);

            if($invoice->client && $invoice->client->workspace && $invoice->client->workspace->slug === 'shalin-designs') {
                $bcc = config('shalin-designs.accounts_mail.bcc', []);
            } else {
                $bcc = config('mail.accounts_mail_bcc', []);
            }
            if (is_array($bcc) && $invoice->sales_person && $invoice->sales_person->email && $sales_mail) {
                array_push($bcc, $invoice->sales_person->email ?? '');
            }

            Mail::mailer(config('mail.accounts_mail_mailer'))
                ->to($to)
                ->bcc($bcc)
                ->send(new PaymentReceiptMail($invoice));

            $user = Auth::user();
            $payment_webhook = '';
            if($payment_method == 'Stripe'){
                $user = User::role('Superadmin')->first();
                $payment_webhook = '(Stripe Webhook)';
            }elseif($payment_method == 'Wise'){
                $user = User::role('Superadmin')->first();
                $payment_webhook = '(Wise Webhook)';
            }
            $workspace_id = $invoice->client->workspace->id ?? 1;
            CronActivityLogHelper::log(
                'invoices.payment_received.mail-sent-to-customer',
                "Mail sent (Thank you for payment on Invoice #{$invoice_number}) to {$customerName} {$payment_webhook}",
                [],
                request(),
                $user,
                $invoice,
                $workspace_id
            );
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }

    public function salespersonSendPaymentReceiptMail(Invoice $invoice,$sales_mail = false)
    {
        try {
            $invoice = $invoice->fresh();
            $invoice->loadMissing('client', 'payments', 'invoice_items', 'currency', 'sales_person', 'client.workspace:id,slug');
            $customerName = $invoice->client->name ?? '';
            $invoice_number = $invoice->invoice_number ?? '';
            $to = $invoice->sales_person->email ?? '';

            if($invoice->client && $invoice->client->workspace && $invoice->client->workspace->slug === 'shalin-designs') {
                $bcc = config('shalin-designs.accounts_mail.bcc', []);
            } else {
                $bcc = config('mail.accounts_mail_bcc', []);
            }

            Mail::mailer(config('mail.accounts_mail_mailer'))
                ->to($to)
                ->bcc($bcc)
                ->send(new PaymentReceiptMail($invoice));

            ActivityLogHelper::log(
                'invoices.payment_received.mail-sent-to-customer',
                "Mail sent (Thank you for payment on Invoice #{$invoice_number}) to {$customerName}",
                [],
                request(),
                Auth::user(),
                $invoice
            );
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }

    public function makeAndStorePDF(Invoice &$invoice, $type = 'invoice')
    {
        $invoice->loadMissing('client.workspace');
        $workspace = $invoice->client->workspace->slug ?? 'iih-global';
        switch ($type) {
            case 'payment_receipt':
                $storageDir     = FileHelper::RECEIPT_PDF;
                $fileNameCol    = 'receipt_file_name';
                $filePathCol    = 'receipt_file_path';
                $fileDiskCol    = 'receipt_file_disk';
                break;
            case 'credit_note':
                $storageDir     = FileHelper::CREDIT_NOTE;
                $fileNameCol    = 'invoice_file_name';
                $filePathCol    = 'invoice_file_path';
                $fileDiskCol    = 'invoice_file_disk';
                break;

            default:
                $storageDir     = FileHelper::INVOICE_PDF;
                $fileNameCol    = 'invoice_file_name';
                $filePathCol    = 'invoice_file_path';
                $fileDiskCol    = 'invoice_file_disk';
                break;
        }

        $filename = $invoice->invoice_number . ".pdf";
        switch ($workspace) {
            case 'shalin-designs':
                $filepath = "shalin-designs/" . $storageDir . '/' . $filename;
                break;

            default:
                $filepath = $storageDir . '/' . $filename;
                break;
        }

        $disk = config('filesystems.default', 'local');

        if (!Storage::disk($disk)->exists($storageDir)) {
            Storage::disk($disk)->makeDirectory($storageDir);
        }

        // if (Storage::exists($filepath)) {
        //     Storage::delete($filepath);
        // }

        Log::info("file created:" . $invoice->invoice_number);
        $this->pdf($invoice, $type)->save($filepath, $disk);

        $invoice->$fileNameCol = $filename;
        $invoice->$filePathCol = $filepath;
        $invoice->$fileDiskCol = $disk;
        $invoice->save();
    }

    /**
     * This function generates a new Credit Note
     * number which doesn't already
     * exists in database
     *
     * @author Krunal Shrimali
     * @return string Newly generated credit note number
     */
    public function credit_note_number()
    {
        $credit_note_number = Invoice::query()
            ->where('type', InvoiceType::CREDIT_NOTE)
            ->whereHas('client', function ($q) {
                $q->where('workspace_id', Auth::user()->workspace_id);
            })
            ->max('invoice_number');
        $start_from = (int) config('custom.credit_note_number_starts_from');
        $start_from = (strlen($start_from) < 3)
            ? str_pad($start_from, 3, '0', STR_PAD_LEFT)
            : $start_from;
        $prefix = config('custom.credit_note_prefix');

        if (!$credit_note_number || substr($credit_note_number, 0, strlen($prefix)) !== $prefix) return $prefix . $start_from;

        $number_part = (int) str_replace($prefix, '', $credit_note_number);

        if (!$number_part) return $prefix . $start_from;

        return
            $prefix .
            ($number_part < (int) $start_from
                ? $start_from
                : ((strlen(++$number_part) < 3)
                    ? str_pad($number_part, 3, '0', STR_PAD_LEFT)
                    : $number_part)
            );
    }
}
