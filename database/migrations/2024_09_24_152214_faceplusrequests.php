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
        Schema::create('faceplus_requests', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->json('request_data')->nullable();
            $table->json('response_data');
            $table->integer('status_code')->nullable();
            $table->string('request_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faceplus_requests');
    }
};
