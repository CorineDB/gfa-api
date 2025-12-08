<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesDePerceptionDeGouvernanceTable extends Migration
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
            $table->foreign('categorieDePerceptionDeGouvernanceId', 'catpg_parent_fk')->references('id')->on('categories_de_perception_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->morphs('categorieable', 'catpg_morph_idx');

            $table->bigInteger('formulaireDePerceptionId')->nullable()->unsigned();
            $table->foreign('formulaireDePerceptionId', 'catpg_form_fk')->references('id')->on('formulaires_de_perception_de_gouvernance')
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
