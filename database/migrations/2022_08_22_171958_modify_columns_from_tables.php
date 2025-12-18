<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsFromTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('projets')){
            Schema::table('projets', function (Blueprint $table) {
                if (Schema::hasColumn('projets', 'poids')) {
                    $table->dropColumn('poids');
                }

            });
        }

        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                if (Schema::hasColumn('composantes', 'tepPrevu')) {
                    $table->dropColumn('tepPrevu');
                }
            }); 
        }


        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if (Schema::hasColumn('activites', 'tepPrevu')) {
                    $table->dropColumn('tepPrevu');
                }
            });
        }


        if(Schema::hasTable('taches')){
            Schema::table('taches', function (Blueprint $table) {
                if (Schema::hasColumn('taches', 'tepPrevu')) {
                    $table->dropColumn('tepPrevu');
                }
            });
        }

        if(Schema::hasTable('suivis')){
            Schema::table('suivis', function (Blueprint $table) {
                if (!Schema::hasColumn('suivis', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
                if (Schema::hasColumn('suivis', 'poidsActuel')) {
                    $table->float('poidsActuel')->change();
                }
            });
        }

        if(Schema::hasTable('e_suivies')){
            Schema::table('e_suivies', function (Blueprint $table) {
                if (!Schema::hasColumn('e_suivies', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
            });
        }

        if(Schema::hasTable('e_suivi_activite_mods')){
            Schema::table('e_suivi_activite_mods', function (Blueprint $table) {
                if (!Schema::hasColumn('e_suivi_activite_mods', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
            });
        }

        if(Schema::hasTable('suivi_indicateurs')) {
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if (!Schema::hasColumn('suivi_indicateurs', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
            });
        }

        if(Schema::hasTable('suivi_indicateur_mods')) {
            Schema::table('suivi_indicateur_mods', function (Blueprint $table) {
                if (!Schema::hasColumn('suivi_indicateur_mods', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
            });
        };

        if(Schema::hasTable('suivi_financiers')) {
            Schema::table('suivi_financiers', function (Blueprint $table) {
                if (!Schema::hasColumn('suivi_financiers', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
            });
        }

        if(Schema::hasTable('suivi_financier_mods')) {
            Schema::table('suivi_financier_mods', function (Blueprint $table) {
                if (!Schema::hasColumn('suivi_financier_mods', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
            });
        }

        if(Schema::hasTable('commentaires')) {
            Schema::table('commentaires', function (Blueprint $table) {
                if (!Schema::hasColumn('commentaires', 'commentaireId')) {
                    
                    $table->bigInteger('commentaireId')->unsigned()->nullable();
                    $table->foreign('commentaireId')->references('id')->on('commentaires')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');
                }
            });
        }

        if(Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'token')) {
                    $table->string('token')->nullable();
                }
                if (!Schema::hasColumn('users', 'link_is_valide')) {
                    $table->boolean('link_is_valide')->default(0);
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

        if(Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'token')) {
                    $table->dropColumn(['token']);
                }
                if (Schema::hasColumn('users', 'link_is_valide')) {
                    $table->dropColumn(['link_is_valide']);
                }
            });
        }


        if(Schema::hasTable('commentaires')) {
            Schema::table('commentaires', function (Blueprint $table) {
                if (Schema::hasColumn('commentaires', 'commentaireId')) {
                    
			        $table->dropForeign('commentaires_commentaireId_foreign');

                    $table->dropColumn('commentaireId');
                }
            });
        }

        if(Schema::hasTable('suivi_financier_mods')) {
            Schema::table('suivi_financier_mods', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_financier_mods', 'commentaire')) {
                    
                    $table->dropColumn('commentaire');
                }
            });
        }


        if(Schema::hasTable('suivi_financiers')) {
            Schema::table('suivi_financiers', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_financiers', 'commentaire')) {
                    
                    $table->dropColumn('commentaire');
                }
            });
        }

        
        if(Schema::hasTable('suivi_indicateur_mods')) {
            Schema::table('suivi_indicateur_mods', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_indicateur_mods', 'commentaire')) {
                    
                    $table->dropColumn('commentaire');
                }
            });
        }

        
        if(Schema::hasTable('suivi_indicateurs')) {
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_indicateurs', 'commentaire')) {
                    
                    $table->dropColumn('commentaire');
                }
            });
        }

        if(Schema::hasTable('e_suivies')) {
            Schema::table('e_suivies', function (Blueprint $table) {
                if (Schema::hasColumn('e_suivies', 'commentaire')) {
                    $table->dropColumn('commentaire');
                }
            });
        }

        if(Schema::hasTable('e_suivi_activite_mods')) {
            Schema::table('e_suivi_activite_mods', function (Blueprint $table) {
                if (Schema::hasColumn('e_suivi_activite_mods', 'commentaire')) {
                    $table->dropColumn('commentaire');
                }
            });
        }       

        if(Schema::hasTable('suivis')) {
            Schema::table('suivis', function (Blueprint $table) {
                if (Schema::hasColumn('suivis', 'commentaire')) {
                    $table->dropColumn('commentaire')->nullable();
                }
            });
        }

    }
}
