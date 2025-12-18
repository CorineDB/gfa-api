<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_teams', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('profilable_id')->unsigned();
            $table->string('profilable_type');

            $table->bigInteger('userId')->unsigned();

            $table->bigInteger('roleId')->unsigned();

            $table->foreign('userId')->references('id')->on('users')
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
