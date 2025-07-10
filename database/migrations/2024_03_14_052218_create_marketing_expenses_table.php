<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_expense_type_id')->constrained('marketing_expense_types', 'id');
            $table->foreignId('currency_id')->constrained('currencies', 'id');
            $table->decimal('amount')->unsigned();
            $table->date('marketing_expense_date');
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
        Schema::dropIfExists('marketing_expenses');
    }
}
