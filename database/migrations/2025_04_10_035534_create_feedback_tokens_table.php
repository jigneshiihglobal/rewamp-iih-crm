<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('feedback_form_token')->nullable();
            $table->boolean('is_used')->nullable()->default('0')->comment('Yes = 1, No = 0');
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
        Schema::dropIfExists('feedback_tokens');
    }
}
