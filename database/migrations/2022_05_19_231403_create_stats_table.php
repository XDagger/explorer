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
			$table->string('version')->comment('node version');
			$table->string('network_type')->comment('mainnet / testnet / devnet');
			$table->bigInteger('blocks')->unsigned();
			$table->bigInteger('main_blocks')->unsigned();
			$table->string('difficulty');
			$table->decimal('supply', 56, 9);
			$table->bigInteger('hashrate')->unsigned();
			$table->timestamp('created_at');
		});
	}

	public function down()
	{
		Schema::dropIfExists('stats');
	}
};
