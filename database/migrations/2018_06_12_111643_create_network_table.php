<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworkTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('network', function (Blueprint $table) {
			$table->increments('id');

			$table->bigInteger('blocks')->unsigned();
			$table->bigInteger('main_blocks')->unsigned();
			$table->string('difficulty');
			$table->bigInteger('supply')->unsigned();
			$table->float('hashrate', 30)->unsigned();

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
		Schema::dropIfExists('network');
	}
}
