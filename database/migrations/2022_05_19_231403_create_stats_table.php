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
			$table->bigInteger('blocks')->unsigned();
			$table->bigInteger('main_blocks')->unsigned();
			$table->string('difficulty');
			$table->decimal('supply', 56, 9);
			$table->bigInteger('hashrate')->unsigned();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('stats');
	}
}
