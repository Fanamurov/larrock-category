<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoryLinkTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('category_link', function(Blueprint $table)
		{
			$table->integer('category_id')->unsigned()->index('category_link_category_id_foreign');
			$table->integer('category_id_link')->unsigned()->index('category_link_category_id_link_foreign');
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
		Schema::dropIfExists('category_link');
	}

}
