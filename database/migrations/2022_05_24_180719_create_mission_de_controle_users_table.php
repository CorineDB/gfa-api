<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionDeControleUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_de_controle_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('roleId')->unsigned();
            $table->bigInteger('userId')->unsigned();
            $table->bigInteger('missionDeControleId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('missionDeControleId')->references('id')->on('mission_de_controles')
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
        Schema::dropIfExists('mission_de_controle_users');
    }
}
