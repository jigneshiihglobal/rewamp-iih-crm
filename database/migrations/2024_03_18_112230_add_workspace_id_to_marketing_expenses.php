<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkspaceIdToMarketingExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->foreignId('workspace_id')->after('marketing_expense_date')->nullable()->constrained('workspaces', 'id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->dropColumn('workspace_id');
        });
    }
}
