<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoicesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoices_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('price');
            $table->string('tax_type', 30)->nullable();
            $table->decimal('tax_rate')->nullable();
            $table->decimal('tax_amount')->nullable();
            $table->decimal('total_price');
            $table->integer('quantity');
            $table->integer('sequence')->unsigned()->default(1);
            $table->boolean('delete_by_admin')->nullable()->default('0')->comment('Yes = 1, No = 0');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoices_items');
    }
}
