<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWisePaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wise_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->nullable();
            $table->longText('payload')->nullable();
            $table->boolean('webhook_link_to_payment_received')->nullable()->default('0')->comment('Yes = 1, No = 0');
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
        Schema::dropIfExists('wise_payment_logs');
    }
}
