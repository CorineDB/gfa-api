<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurValueKeysMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('indicateur_value_keys_mapping')){
            Schema::create('indicateur_value_keys_mapping', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default("int");
                $table->bigInteger('indicateurId')->unsigned()->nullable();
                $table->foreign('indicateurId')->references('id')->on('indicateurs')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('indicateurValueKeyId')->unsigned();
                $table->foreign('indicateurValueKeyId')->references('id')->on('indicateur_value_keys');
                $table->bigInteger('uniteeMesureId')->unsigned();
                $table->foreign('uniteeMesureId')->references('id')->on('unitees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs', 'valeurCibleTotal')){
                    $table->json('valeurCibleTotal')->nullable()->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('valeur_cible_d_indicateurs')){
            Schema::table('valeur_cible_d_indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('valeur_cible_d_indicateurs', 'valeurCible')){
                    $table->json('valeurCible')->nullable()->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('suivi_indicateurs')){
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('suivi_indicateurs', 'valeurRealise')){
                    $table->json('valeurRealise')->nullable()->default(null)->change();
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
        Schema::dropIfExists('indicateur_value_keys_mapping');
    }
}
