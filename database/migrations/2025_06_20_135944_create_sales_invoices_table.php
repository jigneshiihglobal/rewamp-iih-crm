<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('invoice_number')->nullable();
            $table->string('sales_invoice_number')->nullable()->unique();
            $table->boolean('type')->nullable()->default('0')->comment('one-off = 0, subscription = 1');
            $table->boolean('subscription_type')->nullable()->comment('0 = monthly, 1 = yearly');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->decimal('vat_total', 10, 2)->nullable();
            $table->decimal('grand_total', 10, 2)->nullable();
            $table->string('client_name')->nullable();
            $table->unsignedBigInteger('company_detail_id')->nullable();
            $table->string('company')->nullable();
            $table->boolean('status')->nullable()->default('1')->comment('1 = pending, 2 = approve, 3 = rejected, 4 = Mail-send');
            $table->date('mail_send_at')->nullable();
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
        Schema::dropIfExists('sales_invoices');
    }
}
