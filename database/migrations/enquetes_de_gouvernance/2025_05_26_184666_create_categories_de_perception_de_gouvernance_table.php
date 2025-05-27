<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesPerceptionDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_de_perception_de_gouvernance', function (Blueprint $table) {
            $table->id();

            $table->integer('position')->default(0);
            $table->bigInteger('categorieDePerceptionDeGouvernanceId')->nullable()->unsigned();
            $table->foreign('categorieDePerceptionDeGouvernanceId')->references('id')->on('categories_de_perception_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->morphs('categorieable', 'categorieDePerception');

            $table->bigInteger('formulaireDePerceptionId')->nullable()->unsigned();
            $table->foreign('formulaireDePerceptionId')->references('id')->on('formulaires_de_perception_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('categories_de_perception_de_gouvernance');
    }
}
