<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId("currency_id")->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->string('account_holder')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_type')->nullable();
            $table->string('ach_wire_routing_number')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bic')->nullable();
            $table->string('iban')->nullable();
            $table->string('sort_code')->nullable();
            $table->string('wise_address')->nullable();
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
        Schema::dropIfExists('banks');
    }
}
