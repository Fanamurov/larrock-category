<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCategoryLinkTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('category_link', function(Blueprint $table)
		{
			$table->foreign('category_id')->references('id')->on('category')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('category_id_link')->references('id')->on('category')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('category_link', function(Blueprint $table)
		{
			$table->dropForeign('category_link_category_id_foreign');
			$table->dropForeign('category_link_category_id_link_foreign');
		});
	}

}
