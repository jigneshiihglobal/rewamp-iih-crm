<?php

namespace App\Http\Controllers;

use App\Enums\InvoicePaymentStatus;
use App\Helpers\ActivityLogHelper;
use App\Models\Bank;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicePaymentLinkController extends Controller
{
    private $service, $view_config;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
        $this->view_config = [
            'title' => 'Create',
        ];
    }
    public function store(Request $request, Invoice $invoice)
    {
        $valid['payment_link'] = isset($request->payment_link) && !empty($request->payment_link) ? $request->payment_link : '';
        $valid['payment_link_add_at'] = now();

        DB::beginTransaction();

        try {

            $invoice->update($valid);

            if(isset($request->payment_link) && !empty($request->payment_link)){
                ActivityLogHelper::log('invoices.payment-link.updated', 'A payment link added/updated in invoice', ['payment_link' => $request->payment_link], $request, Auth::user(), $invoice);
            }else{
                ActivityLogHelper::log('invoices.payment-link.updated', 'A payment link Removed in invoice', ['payment_link' => $request->payment_link], $request, Auth::user(), $invoice);
            }
            DB::commit();
            $company_detail_id              = intval($invoice->company_detail_id) ?? NULL;
            $bank_company_detail_map        = config('custom.bank_company_detail_map', []);
            $bank_company_detail_map        = !is_array($bank_company_detail_map) ? collect([]) : collect($bank_company_detail_map);
            // $bank_company_detail            = $bank_company_detail_map->firstWhere('company_detail_id', $bank_company_detail_map[0]['company_detail_id']);
            $bank_company_detail            = $bank_company_detail_map->firstWhere('company_detail_id', $company_detail_id);
            
            $bank_detail_id                 = $bank_company_detail['bank_id'] ?? NULL;

            $bank_detail                    = Bank::find($bank_detail_id);
            $bank_detail                    = $bank_detail ?? Bank::where('currency_id', $invoice->currency_id)->orderByDesc('created_at')->first();
            $bank_detail                    = $bank_detail ?? Bank::where('is_default', true)->orderByDesc('created_at')->first();
            $invoice->bank_detail_id        = $bank_detail->id ?? NULL;
            $invoice->save();

            DB::commit();
            $this->service->makeAndStorePDF($invoice);

            return response()
                ->json(
                    [
                        'success' => true,
                        'payment_link' => $request->payment_link,
                    ],
                    200
                );
        } catch (\Throwable $th) {
            DB::rollback();
            Log::info($th);
            return response()
                ->json(
                    [
                        'success' => false,
                        'message' => 'Something went wrong while saving link!',
                        'error' => $th
                    ],
                    500
                );
        }
    }

    public function show(Invoice $invoice)
    {
        try {
            return response()
                ->json([
                    'payment_link' => $invoice->payment_link
                ]);
        } catch (\Throwable $th) {
            return response()
                ->json([
                    'message' => 'Something went wrong while fetching payment link!',
                    'error' => $th,
                    'success' => false,
                ], 500);
        }
    }
}
