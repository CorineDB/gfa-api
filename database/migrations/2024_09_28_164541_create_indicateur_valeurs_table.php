<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurValeursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable("indicateur_valeurs")){
            Schema::create('indicateur_valeurs', function (Blueprint $table) {
                $table->id();
                $table->morphs('indicateur_valueable', 'valueable');
                $table->bigInteger('indicateurValueKeyMapId')->unsigned();
                $table->foreign('indicateurValueKeyMapId', 'indicateurKeyId')->references('id')->on('indicateur_value_keys_mapping')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->string('value')->nullable();
                $table->text('commentaire')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }


        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(!Schema::hasColumn('indicateurs', 'hasMultipleValue')){
                    $table->boolean('hasMultipleValue')->default(false);
                }
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
        Schema::dropIfExists('indicateur_valeurs');
    }
}
