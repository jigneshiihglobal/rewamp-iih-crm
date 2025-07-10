<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactedLeadMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacted_lead_mails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_status_id');
            $table->foreign('lead_status_id')->references('id')->on('lead_statuses');
            $table->string('lead_name',100);
            $table->string('email',100);
            $table->string('day_after',100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacted_lead_mails');
    }
}
