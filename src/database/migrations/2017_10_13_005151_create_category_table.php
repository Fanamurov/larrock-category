<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('category', function(Blueprint $table)
		{
			$table->increments('id');
			$table->char('title');
			$table->text('short')->nullable();
			$table->text('description')->nullable();
			$table->char('description_link')->nullable();
			$table->char('component')->nullable();
			$table->integer('parent')->nullable();
			$table->integer('level')->default(1);
			$table->char('url', 191)->unique();
			$table->integer('sitemap')->default(1);
			$table->integer('rss')->default(0);
			$table->integer('position')->default(1);
			$table->integer('active')->default(1);
			$table->integer('user_id')->unsigned()->nullable()->index('category_user_id_foreign');
			$table->integer('attached')->default(0);
			$table->timestamps();

            $table->index(['url', 'active']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('category');
	}
}