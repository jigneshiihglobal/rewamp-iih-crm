<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('communication')->nullable();
            $table->string('quality_of_work')->nullable();
            $table->string('collaboration')->nullable();
            $table->string('value_for_money')->nullable();
            $table->string('overall_satisfaction')->nullable();
            $table->boolean('recommendation')->nullable()->default('1')->comment('Yes = 1, No = 0');
            $table->text('message_box')->nullable();
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
        Schema::dropIfExists('client_feedbacks');
    }
}
