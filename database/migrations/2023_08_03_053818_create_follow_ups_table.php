<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowUpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sales_person_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('follow_up_at');
            $table->dateTime('send_reminder_at');
            $table->text('content')->nullable();
            $table->json('to')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->json('sales_person_phone')->nullable();
            $table->enum('type', ['email', 'call'])->default('email');
            $table->enum('status', ['pending', 'completed'])->default('pending');
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
        Schema::dropIfExists('follow_ups');
    }
}
