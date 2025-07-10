<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentLogAmountToWisePaymentLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wise_payment_logs', function (Blueprint $table) {
            $table->string('currency')->nullable()->after('payload');
            $table->decimal('amount_received', 8, 2)->after('currency');
            $table->string('sent_at')->nullable()->after('amount_received');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wise_payment_logs', function (Blueprint $table) {
            $table->dropColumn('currency');
            $table->dropColumn('amount_received');
            $table->dropColumn('sent_at');
        });
    }
}
