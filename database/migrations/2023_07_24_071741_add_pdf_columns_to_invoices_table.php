<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfColumnsToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_file_name', 100)->nullable();
            $table->string('receipt_file_name', 100)->nullable();
            $table->string('invoice_file_path', 150)->nullable();
            $table->string('receipt_file_path', 150)->nullable();
            $table->string('invoice_file_disk', 30)->nullable();
            $table->string('receipt_file_disk', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_file_name',
                'receipt_file_name',
                'invoice_file_path',
                'receipt_file_path',
                'invoice_file_disk',
                'receipt_file_disk',
            ]);
        });
    }
}
