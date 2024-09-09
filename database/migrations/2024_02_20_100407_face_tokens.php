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
		Schema::create('face_tokens', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->nullable();
			$table->foreignId('image_id')->constrained();
			$table->foreignId('faceset_id')->nullable()->constrained();
            $table->string('face_token')->nullable();
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
		//
		Schema::dropIfExists('face_tokens');
	}
};
