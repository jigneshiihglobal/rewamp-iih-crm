<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewPantTreeToMoreTreeHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('more_tree_histories', function (Blueprint $table) {
            $table->float('credits_used')->after('email')->nullable();
            $table->string('project_id')->after('credits_used')->nullable();
            $table->string('tree_id')->after('project_id')->nullable();
            $table->string('account_code')->after('tree_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('more_tree_histories', function (Blueprint $table) {
            $table->dropColumn('credits_used');
            $table->dropColumn('project_id');
            $table->dropColumn('tree_id');
            $table->dropColumn('account_code');
        });
    }
}
