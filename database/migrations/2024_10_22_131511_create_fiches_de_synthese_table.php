<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFichesDeSyntheseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('fiches_de_synthese')){
            Schema::create('fiches_de_synthese', function (Blueprint $table) {
                $table->id();
                $table->string("reference");
                $table->enum('type', ['factuel', 'perception']);
                $table->jsonb("synthese");
                $table->dateTime('evaluatedAt')->default(now());
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fiches_de_synthese');
    }
}
