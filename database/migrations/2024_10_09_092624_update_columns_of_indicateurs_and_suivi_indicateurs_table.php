<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsOfIndicateursAndSuiviIndicateursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(Schema::hasColumn('indicateurs', 'indicateurable_id') && Schema::hasColumn('indicateurs', 'indicateurable_type')){
                    $table->dropColumn(['indicateurable_id', 'indicateurable_type']);
                }

                if(!Schema::hasColumn('indicateurs', 'indice')){
                    $table->integer('indice');
                }

                if(Schema::hasColumn('indicateurs', 'sourceDeVerification')){
                    $table->renameColumn('sourceDeVerification', 'sources_de_donnee');
                }

                if(!Schema::hasColumn('indicateurs', 'methode_de_la_collecte')){
                    $table->string('methode_de_la_collecte');
                }

                if(!Schema::hasColumn('indicateurs', 'frequence_de_la_collecte')){
                    $table->string('frequence_de_la_collecte');
                }

                if(!Schema::hasColumn('indicateurs', 'responsable')){
                    $table->string('responsable');
                }

                if(Schema::hasColumn('indicateurs', 'hasMultipleValue')){
                    $table->renameColumn("hasMultipleValue", 'agreger');
                }

            });
        }

        if(Schema::hasTable('suivi_indicateurs')){
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if(!Schema::hasColumn('suivi_indicateurs', 'estValider')){
                    $table->boolean('estValider')->default(true);
                }
            });
        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {

                if(!Schema::hasColumn('categories', 'indice')){
                    $table->integer('indice');
                }

                if(!Schema::hasColumn('categories', 'categorieId')){
                    $table->bigInteger('categorieId')->nullable()->unsigned();
                    $table->foreign('categorieId')->references('id')->on('categories')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
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

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(!(Schema::hasColumn('indicateurs', 'indicateurable_id') && Schema::hasColumn('indicateurs', 'indicateurable_type'))){
                    $table->morphs('indicateurable');
                }

                if(Schema::hasColumn('indicateurs', 'indice')){
                    $table->dropColumn(['indice']);
                }

                if(Schema::hasColumn('indicateurs', 'sources_de_donnee')){
                    $table->renameColumn('sources_de_donnee', 'sourceDeVerification');
                }

                if(Schema::hasColumn('indicateurs', 'methode_de_la_collecte')){
                    $table->dropColumn(['methode_de_la_collecte']);
                }

                if(Schema::hasColumn('indicateurs', 'frequence_de_la_collecte')){
                    $table->dropColumn(['frequence_de_la_collecte']);
                }

                if(Schema::hasColumn('indicateurs', 'responsable')){
                    $table->dropColumn(['responsable']);
                }

                if(Schema::hasColumn('indicateurs', 'agreger')){
                    $table->renameColumn('agreger',"hasMultipleValue", );
                }

            });
        }

        if(Schema::hasTable('suivi_indicateurs')){
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('suivi_indicateurs', 'estValider')){
                    $table->dropColumn(['estValider']);
                }
            });

        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {

                if(Schema::hasColumn('categories', 'indice')){
                    $table->dropColumn(['indice']);
                }

                if(Schema::hasColumn('categories', 'categorieId')){
                    $table->dropForeign(['categorieId']);
                    $table->dropColumn(['categorieId']);
                }
            });
        }
    }
}
