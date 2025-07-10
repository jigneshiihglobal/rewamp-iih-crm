<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageIdToContactedLeadMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacted_lead_mails', function (Blueprint $table) {
            $table->string('message_id')->after('webhook_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacted_lead_mails', function (Blueprint $table) {
            $table->dropColumn('message_id');
        });
    }
}
