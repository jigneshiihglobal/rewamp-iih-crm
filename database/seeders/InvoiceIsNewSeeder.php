<?php

namespace Database\Seeders;

use App\Enums\InvoiceType;
use App\Enums\IsInvoiceNew;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceIsNewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $unpaidNewInvoices = Invoice::query()
                ->select(
                    'invoices.id',
                    'invoices.invoice_number'
                )
                ->where('type', InvoiceType::INVOICE)
                ->where('payment_status', 'unpaid')
                ->where('is_new', IsInvoiceNew::OLD)
                ->whereNull('payment_reminder_sent_at')
                ->whereDoesntHave('credit_note')
                ->whereDoesntHave('payments')
                ->whereHas('client',  function ($q) {
                    $q->where('clients.workspace_id', '1'); // IIH Global Invoices
                })
                ->get();

            $this->command->line($unpaidNewInvoices->count() . " new invoices found!");

            if(!$unpaidNewInvoices->count()) {
                DB::rollback();
                return 0;
            }

            $this->command->table(
                [
                    'Invoice ID',
                    'Invoice Number',
                    'Encrypted Invoice ID',
                ],
                $unpaidNewInvoices->toArray()
            );

            if(! $this->command->confirm('Are you sure you want to mark above listed invoices as \'NEW\'?')) {
                $this->command->line("Operation cancelled!");
                DB::rollback();
                return 0;
            }

            $unpaidNewInvoicesIdsArr = $unpaidNewInvoices->pluck('id')->toArray();

            DB::table('invoices')
                ->whereIn('id', $unpaidNewInvoicesIdsArr)
                ->update(['is_new' => IsInvoiceNew::NEW]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
