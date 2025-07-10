<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullableAndNewToContactedLeadMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacted_lead_mails', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_status_id')->nullable()->change();
            $table->string('mail_subject')->nullable();
            $table->string('lead_name',100)->nullable()->change();
            $table->string('email',100)->nullable()->change();
            $table->string('day_after',100)->nullable()->change();
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
            $table->unsignedBigInteger('lead_status_id');
            $table->dropColumn('mail_subject')->nullable();
            $table->string('lead_name',100);
            $table->string('email',100);
            $table->string('day_after',100);
        });
    }
}
