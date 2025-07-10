<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteReminderAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_reminder_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_reminder_id')->constrained('note_reminders',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->float('received_amount', 8, 2)->nullable();
            $table->float('pending_amount', 8, 2)->nullable();
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
        Schema::dropIfExists('note_reminder_amounts');
    }
}
