<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
			$table->string('nom', 255);
			$table->string('prenom', 255)->nullable();
			$table->string('email', 255)->unique();
			$table->string('password', 255);
			$table->string('contact', 255)->unique();
			$table->string('poste', 255)->nullable();
			$table->string('type', 255);
            $table->timestamp('emailVerifiedAt')->nullable();
            $table->rememberToken();
			$table->integer('code')->nullable();
            $table->bigInteger('profilable_id')->unsigned();
            $table->string('profilable_type')->nullable();
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
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
        Schema::dropIfExists('users');
    }
}
