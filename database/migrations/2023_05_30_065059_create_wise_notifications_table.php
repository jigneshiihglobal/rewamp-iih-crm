<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWiseNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wise_notifications', function (Blueprint $table) {
            $table->id();
            $table->json('data')->nullable(true);
            $table->string('subscription_id', 100)->nullable(true);
            $table->string('event_type', 50)->nullable(true);
            $table->string('schema_version', 30)->nullable(true);
            $table->dateTime('sent_at')->nullable(true);
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
        Schema::dropIfExists('wise_notifications');
    }
}
