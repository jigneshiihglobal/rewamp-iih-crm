<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferFlagAndDateToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dateTime('refer_earn_mail_at')->nullable(true);
            $table->enum('is_refer_earn_mail', array(1, 0))->default(0)->nullable()->comment('1 sent_yes, 0 sent_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('refer_earn_mail_at');
            $table->dropColumn('is_refer_earn_mail');
        });
    }
}
