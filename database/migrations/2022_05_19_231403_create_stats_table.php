<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	public function up()
	{
		Schema::create('stats', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->boolean('synchronized');
			$table->string('version')->comment('node version');
			$table->string('network_type')->comment('mainnet / testnet / devnet');
			$table->bigInteger('blocks')->unsigned();
			$table->bigInteger('network_blocks')->unsigned();
			$table->bigInteger('main_blocks')->unsigned();
			$table->bigInteger('network_main_blocks')->unsigned();
			$table->string('difficulty');
			$table->string('network_difficulty');
			$table->decimal('supply', 56, 9);
			$table->decimal('network_supply', 56, 9);
			$table->decimal('block_reward', 56, 9);
			$table->bigInteger('hashrate')->unsigned();
			$table->bigInteger('network_hashrate')->unsigned();
			$table->text('connections');
			$table->timestamp('created_at', 3)->nullable();
		});
	}

	public function down()
	{
		Schema::dropIfExists('stats');
	}
};
