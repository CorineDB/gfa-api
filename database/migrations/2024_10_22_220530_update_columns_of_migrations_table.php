<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsOfMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('options_de_reponse')){
            Schema::table('options_de_reponse', function (Blueprint $table) {
                if(Schema::hasColumn('options_de_reponse', 'note')){
                    $table->dropColumn('note');
                }
            });
        }

        if(Schema::hasTable('organisations')){
            Schema::table('organisations', function (Blueprint $table) {
                if(!Schema::hasColumn('organisations', 'type')){
                    $table->enum('type', ['osc', 'osc_fosir', 'structure_etatique']);
                }
                if(!Schema::hasColumn('organisations', 'pays')){
                    $table->string('pays')->default('Bénin');
                }
                if(!Schema::hasColumn('organisations', 'departement')){
                    $table->string('departement')->default('Litoral');
                }
                if(!Schema::hasColumn('organisations', 'commune')){
                    $table->string('commune')->default('Cotonou');
                }
                if(!Schema::hasColumn('organisations', 'arrondissement')){
                    $table->string('arrondissement')->default('Sènadé');
                }
                if(!Schema::hasColumn('organisations', 'quartier')){
                    $table->string('quartier')->default('Sènadé');
                }
                if(!Schema::hasColumn('organisations', 'secteurActivite')){
                    $table->string('secteurActivite')->default('Environnement');
                }
                if(!Schema::hasColumn('organisations', 'longitude')){
                    $table->string('longitude');
                }
                if(!Schema::hasColumn('organisations', 'latitude')){
                    $table->string('latitude');
                }
            });
        }

        if(!Schema::hasTable("formulaire_options_de_reponse")){
            
            Schema::create('formulaire_options_de_reponse', function (Blueprint $table) {
                $table->id();
            
                $table->integer('point');
                $table->bigInteger('formulaireDeGouvernanceId')->unsigned();
                $table->foreign('formulaireDeGouvernanceId')->references('id')->on('formulaires_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                    
                $table->bigInteger('optionId')->unsigned();
                $table->foreign('optionId')->references('id')->on('options_de_reponse')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if(!Schema::hasTable("evaluation_organisations")){
            
            Schema::create('evaluation_organisations', function (Blueprint $table) {
                $table->id();
            
                $table->integer('nbreParticipants')->default(0);
                $table->bigInteger('evaluationDeGouvernanceId')->unsigned();
                $table->foreign('evaluationDeGouvernanceId')->references('id')->on('evaluations_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('organisationId')->unsigned();
                $table->foreign('organisationId')->references('id')->on('organisations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if(!Schema::hasTable("fond_organisations")){
            Schema::create('fond_organisations', function (Blueprint $table) {
                $table->id();
                $table->integer('budgetAllouer')->default(0);
                $table->bigInteger('fondId')->unsigned();
                $table->foreign('fondId')->references('id')->on('fonds')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('organisationId')->unsigned();
                $table->foreign('organisationId')->references('id')->on('organisations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if(Schema::hasTable('indicateurs_de_gouvernance')){
            Schema::table('indicateurs_de_gouvernance', function (Blueprint $table) {

                if((Schema::hasColumn('indicateurs_de_gouvernance', 'principeable_id') && Schema::hasColumn('indicateurs_de_gouvernance', 'principeable_type'))){
                    $table->dropColumn(["principeable_id", "principeable_type"]);
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
        if(Schema::hasTable('options_de_reponse')){
            Schema::table('options_de_reponse', function (Blueprint $table) {

                if(!Schema::hasColumn('options_de_reponse', 'note')){
                    $table->string('note');
                }
            });
        }

        if(Schema::hasTable('organisations')){
            Schema::table('organisations', function (Blueprint $table) {
                if(Schema::hasColumn('organisations', 'type')){
                    $table->dropColumn('type');
                }
                if(Schema::hasColumn('organisations', 'pays')){
                    $table->dropColumn('pays');
                }
                if(Schema::hasColumn('organisations', 'departement')){
                    $table->dropColumn('departement');
                }
                if(Schema::hasColumn('organisations', 'commune')){
                    $table->dropColumn('commune');
                }
                if(Schema::hasColumn('organisations', 'arrondissement')){
                    $table->dropColumn('arrondissement');
                }
                if(Schema::hasColumn('organisations', 'quartier')){
                    $table->dropColumn('quartier');
                }
                if(Schema::hasColumn('organisations', 'secteurActivite')){
                    $table->dropColumn('secteurActivite');
                }
                if(Schema::hasColumn('organisations', 'longitude')){
                    $table->dropColumn('longitude');
                }
                if(Schema::hasColumn('organisations', 'latitude')){
                    $table->dropColumn('latitude');
                }
            });
        }

        if(Schema::hasTable('indicateurs_de_gouvernance')){
            Schema::table('indicateurs_de_gouvernance', function (Blueprint $table) {
                if((!Schema::hasColumn('indicateurs_de_gouvernance', 'principeable_id') && Schema::hasColumn('indicateurs_de_gouvernance', 'principeable_type'))){
                    $table->morphs('principeable');
                }

            });
        }
    }
}
