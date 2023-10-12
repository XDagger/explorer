<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	public function up()
	{
		Schema::create('block_notifications', function (Blueprint $table) {
			$table->string('id', 64)->primary();
			$table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');
			$table->unsignedInteger('timeout')->default(30);
			$table->text('message');
			$table->timestamp('created_at')->nullable();
			$table->timestamp('show_from')->nullable();
			$table->timestamp('show_to')->nullable();
		});
	}

	public function down()
	{
		Schema::dropIfExists('block_notifications');
	}
};
