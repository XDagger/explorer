<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	public function up()
	{
		Schema::create('blocks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('identifier')->unique();
			$table->timestamp('effective_at')->nullable();
			$table->bigInteger('height')->unsigned()->nullable();
			$table->string('state', 20)->nullable();
			$table->string('kind', 20)->nullable();
			$table->string('hash', 64)->nullable();
			$table->string('address', 32)->nullable();
			$table->string('difficulty')->nullable();
			$table->string('remark')->nullable();
			$table->timestamp('expires_at');
		});

		Schema::create('block_addresses', function (Blueprint $table) {
			$table->foreignId('block_id')->constrained();
			$table->enum('direction', ['input', 'output', 'earning']);
			$table->string('address', 32);
			$table->decimal('amount', 56, 9);
			$table->string('remark')->nullable();
			$table->timestamp('effective_at');
		});

		Schema::create('block_transactions', function (Blueprint $table) {
			$table->foreignId('block_id')->constrained();
			$table->enum('direction', ['input', 'output', 'fee']);
			$table->string('address', 32);
			$table->decimal('amount', 56, 9);
		});

		Schema::create('balances', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('identifier')->unique();
			$table->decimal('balance', 56, 9)->nullable();
			$table->timestamp('expires_at');
		});
	}

	public function down()
	{
		Schema::dropIfExists('block_transactions');
		Schema::dropIfExists('block_addresses');
		Schema::dropIfExists('blocks');
	}
}
