<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faces', function (Blueprint $table) {
            $table->id();
            $table->string('face_token')->unique();
            $table->unsignedBigInteger('image_id');

            $table->unsignedBigInteger('faceplusrequest_id');

            $table->json('attributes')->nullable();
            $table->json('face_rectangle')->nullable();
            $table->json('landmarks')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('faceset_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('faceplusrequest_id')->references('id')->on('faceplus_requests');
            $table->foreign('image_id')->references('id')->on('images');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('faceset_id')->references('id')->on('facesets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faces');
    }
};
