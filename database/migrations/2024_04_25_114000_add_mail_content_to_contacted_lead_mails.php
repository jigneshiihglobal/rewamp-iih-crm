<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMailContentToContactedLeadMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacted_lead_mails', function (Blueprint $table) {
            $table->text('mail_content')->after('mail_subject')->nullable();
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
            $table->dropColumn('mail_content');
        });
    }
}
