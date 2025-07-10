<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadStatusToContactedLeadMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacted_lead_mails', function (Blueprint $table) {
            $table->string('lead_status_event')->after('day_after')->nullable();
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
            $table->dropColumn('lead_status_event');
        });
    }
}
