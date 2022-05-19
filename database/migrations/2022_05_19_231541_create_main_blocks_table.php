<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	public function up()
	{
		Schema::create('main_blocks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('height')->unsigned();
			$table->string('address', 32)->index();
			$table->timestamp('mined_at');
			$table->decimal('balance', 56, 9);
			$table->string('remark')->nullable();
		});
	}

	public function down()
	{
		Schema::dropIfExists('main_blocks');
	}
}
