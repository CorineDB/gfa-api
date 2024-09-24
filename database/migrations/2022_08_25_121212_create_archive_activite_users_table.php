<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveActiviteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_activite_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('activiteId')->unsigned();
			$table->bigInteger('userId')->unsigned();
            $table->string('type');
			$table->foreign('activiteId')->references('id')->on('archive_activites')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archive_activite_users');
    }
}
