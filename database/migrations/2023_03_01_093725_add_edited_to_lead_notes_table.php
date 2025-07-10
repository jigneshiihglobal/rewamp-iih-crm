<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEditedToLeadNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->foreignId('last_edited_by_user_id')->nullable()->constrained('users', 'id');
            $table->timestamp('last_edited_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_edited_by_user_id');
            $table->dropColumn('last_edited_at');
        });
    }
}
