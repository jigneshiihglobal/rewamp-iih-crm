<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeLeadTypeIdNullableInLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('leads_lead_type_id_foreign');
            $table->unsignedBigInteger('lead_type_id')->nullable(true)->change();
            $table->foreign('lead_type_id')->references('id')->on('lead_types')->onDelete('restrict');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('leads_lead_type_id_foreign');
            $table->unsignedBigInteger('lead_type_id')->nullable(false)->change();
            $table->foreign('lead_type_id')->references('id')->on('lead_types')->onDelete('restrict');
        });
        Schema::enableForeignKeyConstraints();
    }
}
