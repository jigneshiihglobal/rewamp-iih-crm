<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionRelatedColumnsToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->smallInteger('invoice_type')->default('0')->comment('0 - one-off, 1 - subscription')->change(); // 0 for one off invoice, 1 for subscription
            $table->smallInteger('subscription_type')->nullable(true)->after('invoice_type')->comment('0 - monthly, 1 - yearly'); // 0 for monthly invoice, 1 for yearly
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
            $table->dropColumn('subscription_type');
        });
    }
}
