<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	public function up()
	{
		Schema::create('blocks', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->string('state', 20)->nullable();
			$table->bigInteger('height')->unsigned()->nullable();
			$table->decimal('balance', 56, 9)->nullable();
			$table->string('type', 20)->nullable();
			$table->string('hash', 64)->nullable();
			$table->string('address', 32)->nullable();
			$table->string('difficulty')->nullable();
			$table->string('timestamp')->nullable();
			$table->string('flags')->nullable();
			$table->string('remark')->nullable();
			$table->timestamp('created_at', 3)->nullable();
			$table->timestamp('expires_at', 3)->nullable();
		});

		Schema::create('block_transactions', function (Blueprint $table) {
			$table->string('block_id');
			$table->bigInteger('ordering')->unsigned()->index();
			$table->enum('view', ['wallet', 'transaction']);
			$table->enum('direction', ['input', 'output', 'earning', 'fee', 'snapshot']);
			$table->string('address', 32);
			$table->decimal('amount', 56, 9);
			$table->string('remark')->nullable();
			$table->timestamp('created_at', 3)->nullable();

			$table->foreign('block_id')->references('id')->on('blocks');
		});

		Schema::create('balances', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->string('state', 20)->nullable();
			$table->decimal('balance', 56, 9)->nullable();
			$table->timestamp('expires_at', 3)->nullable();
		});
	}

	public function down()
	{
		Schema::dropIfExists('block_transactions');
		Schema::dropIfExists('blocks');
		Schema::dropIfExists('balances');
	}
};
