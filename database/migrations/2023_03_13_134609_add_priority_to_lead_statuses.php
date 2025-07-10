<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPriorityToLeadStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->unsignedSmallInteger('priority');
        });
        // Set priority to existing records
        DB::statement('SET @row_number = 0');
        DB::statement('UPDATE lead_statuses SET priority = @row_number:=@row_number+1');

        // Add unique constraint to priority column
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->unique('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->dropUnique(['priority']);
            $table->dropColumn(['priority']);
        });
    }
}
