<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('programmes')){
            Schema::table('programmes', function (Blueprint $table) {
                if(Schema::hasColumn('programmes', 'budgetNational')){
                    $table->bigInteger('budgetNational')->nullable()->change();
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'pret')){
                    $table->bigInteger('pret')->nullable()->change();
                }

                if(Schema::hasColumn('activites', 'tepPrevu')){
                    $table->bigInteger('tepPrevu')->nullable()->change();
                }
            });
        }

        if(Schema::hasTable('bailleurs')){
            Schema::table('bailleurs', function (Blueprint $table) {
                if(Schema::hasColumn('bailleurs', 'pays')){
                    $table->string('pays')->nullable()->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                
                if(Schema::hasColumn('composantes', 'poids')){
                    $table->integer('poids')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('composantes', 'pret')){
                    $table->integer('pret')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('composantes', 'budgetNational')){
                    $table->integer('budgetNational')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('composantes', 'tepPrevu')){
                    $table->integer('tepPrevu')->nullable()->default(0)->change();
                }

            });
        }

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs', 'valeurDeBase')){
                    $table->json('valeurDeBase')->nullable()->default(null)->change();
                }

                if(Schema::hasColumn('indicateurs', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->nullable()->default(null)->change();
                }

                if(!(Schema::hasColumn('indicateurs', 'indicateurable_id') && Schema::hasColumn('indicateurs', 'indicateurable_type'))){
                    $table->morphs('indicateurable');
                }

                if(Schema::hasColumn('indicateurs', 'uniteeMesureId')){
                    $table->bigInteger('uniteeMesureId')->unsigned()->nullable()->default(null)->change();
                }

                if(!Schema::hasColumn('indicateurs', 'type_de_variable')){
                    $table->enum('type_de_variable', ["quantitatif", "qualitatif", "dichotomique"]);
                }


            });
        }

        if(Schema::hasTable('projets')){
            Schema::table('projets', function (Blueprint $table) {

                if(Schema::hasColumn('projets', 'pret')){
                    $table->bigInteger('pret')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('projets', 'budgetNational')){
                    $table->bigInteger('budgetNational')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('projets', 'ville')){
                    $table->string('ville')->nullable()->default(null)->change();
                }

                if(Schema::hasColumn('projets', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->nullable()->default(null)->change();
                }

                if(!(Schema::hasColumn('projets', 'projetable_id') && Schema::hasColumn('projets', 'projetable_type'))){
                    $table->morphs('projetable');
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
        if(Schema::hasTable('programmes')){
            Schema::table('programmes', function (Blueprint $table) {
                if(Schema::hasColumn('programmes', 'budgetNational')){
                    $table->bigInteger('budgetNational')->nullable(false)->change();
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'pret')){
                    $table->bigInteger('pret')->nullable(false)->change();
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'tepPrevu')){
                    $table->bigInteger('tepPrevu')->nullable(false)->change();
                }
            });
        }

        if(Schema::hasTable('bailleurs')){
            Schema::table('bailleurs', function (Blueprint $table) {
                if(Schema::hasColumn('bailleurs', 'pays')){
                    $table->string('pays')->nullable(false)->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                
                if(Schema::hasColumn('composantes', 'poids')){
                    $table->integer('poids')->nullable(false)->change();
                }

                if(Schema::hasColumn('composantes', 'pret')){
                    $table->integer('pret')->nullable(false)->change();
                }

                if(Schema::hasColumn('composantes', 'budgetNational')){
                    $table->integer('budgetNational')->nullable(false)->change();
                }

                if(Schema::hasColumn('composantes', 'tepPrevu')){
                    $table->integer('tepPrevu')->nullable(false)->change();
                }

            });
        }

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs', 'valeurDeBase')){
                    $table->integer('valeurDeBase')->change();
                }

                if(Schema::hasColumn('indicateurs', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->change();
                }

                if((Schema::hasColumn('indicateurs', 'indicateurable_id') && Schema::hasColumn('indicateurs', 'indicateurable_type'))){
                    $table->dropColumn(["indicateurable_id", "indicateurable_type"]);
                }

                if(Schema::hasColumn('indicateurs', 'uniteeMesureId')){
                    $table->bigInteger('uniteeMesureId')->unsigned()->change();
                }

                if(Schema::hasColumn('indicateurs', 'type_de_variable')){
                    $table->dropColumn("type_de_variable");
                }

            });
        }

        if(Schema::hasTable('projets')){
            Schema::table('projets', function (Blueprint $table) {

                if(Schema::hasColumn('projets', 'pret')){
                    $table->bigInteger('pret')->change();
                }

                if(Schema::hasColumn('projets', 'budgetNational')){
                    $table->bigInteger('budgetNational')->change();
                }

                if(Schema::hasColumn('projets', 'ville')){
                    $table->string('ville')->change();
                }

                if(Schema::hasColumn('projets', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->change();
                }

                if((Schema::hasColumn('projets', 'projetable_id') && Schema::hasColumn('projets', 'projetable_type'))){
                    $table->dropColumn(["projetable_id", "projetable_type"]);
                }

            });
        }
    }
}
