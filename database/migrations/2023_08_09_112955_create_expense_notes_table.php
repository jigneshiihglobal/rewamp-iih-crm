<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users',  'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('note');
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
        Schema::dropIfExists('expense_notes');
    }
}
