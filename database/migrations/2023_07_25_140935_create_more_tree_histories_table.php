<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoreTreeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('more_tree_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients', 'id');
            $table->boolean('status')->default(false);
            $table->string('name', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('certificate_id')->nullable();
            $table->string('certificate_url')->nullable();
            $table->timestamp('mail_sent_at')->nullable();
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
        Schema::dropIfExists('more_tree_histories');
    }
}
