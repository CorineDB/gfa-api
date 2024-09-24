<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bailleurId')->unsigned();
            $table->string('codePta');
            $table->bigInteger('programmeId')->unsigned();
            $table->timestamps();
			$table->softDeletes();
            $table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codes');
    }
}
