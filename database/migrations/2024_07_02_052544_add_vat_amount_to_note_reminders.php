<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatAmountToNoteReminders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('note_reminders', function (Blueprint $table) {
            $table->float('without_vat', 8, 2)->nullable()->after('assign_client_id');
            $table->float('vat_amount', 8, 2)->nullable()->after('without_vat');
            $table->boolean('vat_status')->default(0)->after('total_amount')->comment('20% vat add in total amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('note_reminders', function (Blueprint $table) {
            $table->dropColumn('without_vat');
            $table->dropColumn('vat_amount');
            $table->dropColumn('vat_status');
        });
    }
}
