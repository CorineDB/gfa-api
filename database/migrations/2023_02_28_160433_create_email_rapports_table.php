<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailRapportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_rapports', function (Blueprint $table) {
            $table->id();
            $table->string('objet');
            $table->longText('rapport');
            $table->string('destinataires');
            $table->bigInteger('userId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')
				  ->onDelete('cascade')
				  ->onUpdate('cascade');
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
        Schema::dropIfExists('email_rapports');
    }
}
