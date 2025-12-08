<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('roleId')->unsigned();
            $table->bigInteger('userId')->unsigned();
            $table->foreign('roleId')->references('id')->on('roles')
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
        Schema::dropIfExists('role_users');

    }
}
