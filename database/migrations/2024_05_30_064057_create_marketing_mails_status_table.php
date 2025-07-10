<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingMailsStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing_mails_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contacted_lead_mail_id')->constrained('contacted_lead_mails',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('webhook_id')->nullable();
            $table->string('lead_status_event')->nullable();
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
        Schema::dropIfExists('marketing_mails_status');
    }
}
