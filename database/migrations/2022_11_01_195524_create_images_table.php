<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('image_url');
            $table->string('image_url_secure');
            $table->string('size'); //readable size
            $table->string('filetype');
            $table->string('originalFilename');
            $table->string('publicId');
            $table->string('extension');
            $table->string('width');
            $table->string('height');
            $table->string('timeUploaded');
            $table->foreignId('person_id')->nullable()->constrained();
            $table->string('faceplusimage_id')->nullable();
            $table->string('md5');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('person_id')->references('id')->on('people');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
};
