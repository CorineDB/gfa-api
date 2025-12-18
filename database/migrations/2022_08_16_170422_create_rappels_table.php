<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRappelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rappels', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description');
            $table->integer('frequence');
            $table->date('dateAvant');
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
        Schema::dropIfExists('rappels');
    }
}
