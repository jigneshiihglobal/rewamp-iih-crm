<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoveHashFromSalesInvoiceNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invoices = DB::table('sales_invoices')
            ->select('id', 'sales_invoice_number')
            ->where('sales_invoice_number', 'like', '#%')
            ->get();

        foreach ($invoices as $invoice) {
            $cleaned = ltrim($invoice->sales_invoice_number, '#');
            
            DB::statement("
                UPDATE sales_invoices
                SET sales_invoice_number = ?
                WHERE id = ?
            ", [$cleaned, $invoice->id]);
        }
    }
}
