<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniteeDeGestionUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unitee_de_gestion_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('roleId')->unsigned();
            $table->bigInteger('userId')->unsigned();
            $table->bigInteger('uniteDeGestionId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('uniteDeGestionId')->references('id')->on('unitee_de_gestions')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('roleId')->references('id')->on('roles')
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
        Schema::dropIfExists('unitee_de_gestion_users');
    }
}
