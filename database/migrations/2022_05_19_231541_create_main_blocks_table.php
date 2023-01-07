<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	public function up()
	{
		Schema::create('main_blocks', function (Blueprint $table) {
			$table->string('address', 32)->primary();
			$table->bigInteger('height')->unsigned();
			$table->decimal('balance', 56, 9);
			$table->string('remark')->nullable();
			$table->timestamp('created_at', 3)->nullable();
		});
	}

	public function down()
	{
		Schema::dropIfExists('main_blocks');
	}
};
