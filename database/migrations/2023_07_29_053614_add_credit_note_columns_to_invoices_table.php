<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditNoteColumnsToInvoicesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('due_date')->nullable(true)->change();
            $table->enum('type', ['0', '1'])->default('0')->after('id')->comment("'0' - Invoice, '1' - Credit note")->index();
            $table->foreignId('parent_invoice_id')->nullable(true)->constrained('invoices', 'id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('due_date')->nullable(false)->change();
            $table->dropForeign(['parent_invoice_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'parent_invoice_id']);
        });
    }
}
