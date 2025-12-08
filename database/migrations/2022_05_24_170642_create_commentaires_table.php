<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentairesTable extends Migration {

	public function up()
	{
		Schema::create('commentaires', function(Blueprint $table) {
			$table->id();
			$table->string('contenu');
			$table->string('commentable_type');
			$table->bigInteger('commentable_id');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('commentaires');
	}
}