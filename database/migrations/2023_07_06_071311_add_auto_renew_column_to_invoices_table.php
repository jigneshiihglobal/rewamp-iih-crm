<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoRenewColumnToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('subscription_status', ['created', 'auto_created', 'cancelled'])->default('created');
            $table->foreignId('original_subscription_invoice_id')->nullable(true)->constrained('invoices', 'id');
            $table->foreignId('parent_subscription_invoice_id')->nullable(true)->constrained('invoices', 'id');
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
            $table->dropForeign(['original_subscription_invoice_id']);
            $table->dropForeign(['parent_subscription_invoice_id']);
            $table->dropColumn(['subscription_status', 'original_subscription_invoice_id', 'parent_subscription_invoice_id']);
        });
    }
}
