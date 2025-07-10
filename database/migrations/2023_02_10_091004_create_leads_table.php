<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId("lead_source_id")->constrained();
            $table->foreignId("lead_type_id")->constrained();
            $table->foreignId("lead_status_id")->constrained();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('landline')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('housenumber')->nullable();
            $table->string('country')->nullable();
            $table->string('postcode')->nullable();
            $table->text('requirement')->nullable();
            $table->text('project_budget')->nullable();
            $table->string('source')->nullable();
            $table->string('lead_type')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('assigned_to')->nullable();
            $table->string('cb_time')->nullable();
            $table->string('au_file')->nullable();
            $table->string('weatherseal')->nullable();
            $table->string('penicuik')->nullable();
            $table->string('energyhypermarket')->nullable();
            $table->string('sthelens')->nullable();
            $table->string('zenith')->nullable();
            $table->string('sold')->default(0);
            $table->string('linkdin_url', 255)->nullable();
            $table->string('skype_id', 255)->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('transfer_hotkey')->nullable()->default(0);
            $table->string('current_user')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
}
