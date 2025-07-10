<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->text('note')->nullable();
            $table->decimal('discount')->default(0);
            $table->integer('invoice_type'); // 0 for one off invoice, 1 for subscription
            $table->decimal('sub_total');
            $table->decimal('vat_total');
            $table->decimal('grand_total');
            $table->foreignId('client_id')->constrained('clients');
            $table->timestamp('due_date');
            $table->timestamp('invoice_date')->useCurrent();
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
        Schema::dropIfExists('invoices');
    }
}
