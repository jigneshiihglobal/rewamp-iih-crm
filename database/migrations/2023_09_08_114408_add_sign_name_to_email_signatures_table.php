<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignNameToEmailSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_signatures', function (Blueprint $table) {
            $table->string('sign_name', 100)->nullable()->default("IIH Global");
            $table->boolean('is_default')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_signatures', function (Blueprint $table) {
            $table->dropColumn(['sign_name', 'is_default']);
        });
    }
}
