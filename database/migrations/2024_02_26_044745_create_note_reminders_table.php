<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->string('assign_client_id')->nullable();
            $table->foreignId('last_edited_by_user_id')->nullable(true)->constrained('users',  'id')->cascadeOnUpdate()->nullOnDelete();
            $table->dateTime('last_edited_at')->nullable(true);
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
        Schema::dropIfExists('note_reminders');
    }
}
