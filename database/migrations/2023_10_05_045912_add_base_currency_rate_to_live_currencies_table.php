<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaseCurrencyRateToLiveCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_currencies', function (Blueprint $table) {
            $table->double('base_currency_rate',15,4)->after('currency_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('live_currencies', function (Blueprint $table) {
            $table->dropColumn('base_currency_rate');
        });
    }
}
