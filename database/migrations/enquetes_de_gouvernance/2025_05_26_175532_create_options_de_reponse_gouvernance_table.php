<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsDeReponseGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options_de_reponse_gouvernance', function (Blueprint $table) {
            $table->id();
            $table->text('libelle');
            $table->text('slug');
            $table->longText('description')->nullable();
			$table->enum('type', ['factuel', 'perception'])->default("perception");
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unique(['libelle', 'slug', 'programmeId']);
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
        Schema::dropIfExists('options_de_reponse_gouvernance');
    }
}
