<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromColumnsToSmtpCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smtp_credentials', function (Blueprint $table) {
            $table->string('from_name', 50)->nullable();
            $table->string('from_address', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smtp_credentials', function (Blueprint $table) {
            $table->dropColumn(['from_name', 'from_address']);
        });
    }
}
