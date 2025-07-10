<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientReviewMailSendHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_review_mail_send_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('review_mail_send_date_time')->nullable();
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
        Schema::dropIfExists('client_review_mail_send_histories');
    }
}
