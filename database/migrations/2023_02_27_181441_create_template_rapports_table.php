<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateRapportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_rapports', function (Blueprint $table) {
            $table->id();
            $table->longText('rapport')->nullable();
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
        Schema::dropIfExists('template_rapports');
    }
}
