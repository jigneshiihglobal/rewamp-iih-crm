<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients', 'id');
            $table->string('project_name', 100);
            $table->foreignId('expense_type_id')->constrained('expense_types', 'id');
            $table->foreignId('expense_sub_type_id')->constrained('expense_sub_types', 'id');
            $table->decimal('amount')->unsigned();
            $table->foreignId('currency_id')->constrained('currencies', 'id');
            $table->enum('type', ['0', '1'])->default('0')->comment('0: One-off, 1: Recurring');
            $table->enum('frequency', ['0', '1'])->nullable()->comment('0: Monthly, 1: Yearly');
            $table->date('expense_date');
            $table->date('remind_at');
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
        Schema::dropIfExists('expenses');
    }
}
