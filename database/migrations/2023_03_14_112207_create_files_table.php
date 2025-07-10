<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fileable_id');
            $table->string('fileable_type');
            $table->foreignId('uploaded_by_user_id')->constrained('users', 'id');
            $table->string('name');
            $table->string('filename');
            $table->string('mime');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('collection')->nullable();
            $table->unsignedBigInteger('size');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['fileable_id', 'fileable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
