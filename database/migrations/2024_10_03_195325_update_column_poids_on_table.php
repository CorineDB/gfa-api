<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnPoidsOnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                if(Schema::hasColumn('composantes', 'poids')){
                    $table->float('poids')->default(100)->change();
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'poids')){
                    $table->float('poids')->default(100)->change();
                }
            });
        }

        if(Schema::hasTable('taches')){
            Schema::table('taches', function (Blueprint $table) {
                if(Schema::hasColumn('taches', 'poids')){
                    $table->float('poids')->default(100)->change();
                }
            });
        }



        if(Schema::hasTable('archive_composantes')){
            Schema::table('archive_composantes', function (Blueprint $table) {
                if(Schema::hasColumn('archive_composantes', 'poids')){
                    $table->float('poids')->default(100)->change();
                }
            });
        }

        if(Schema::hasTable('archive_activites')){
            Schema::table('archive_activites', function (Blueprint $table) {
                if(Schema::hasColumn('archive_activites', 'poids')){
                    $table->float('poids')->default(100)->change();
                }
            });
        }

        if(Schema::hasTable('archive_taches')){
            Schema::table('archive_taches', function (Blueprint $table) {
                if(Schema::hasColumn('archive_taches', 'poids')){
                    $table->float('poids')->default(100)->change();
                }
            });
        }

        if(Schema::hasTable('suivi_financiers')){
            Schema::table('suivi_financiers', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_financiers', 'suivi_financierable_type') &&
                    Schema::hasColumn('suivi_financiers', 'suivi_financierable_id')) {

                    // Make both morph columns nullable
                    $table->string('suivi_financierable_type')->nullable()->change();
                    $table->unsignedBigInteger('suivi_financierable_id')->nullable()->change();
                }

            });
        }

        if(Schema::hasTable('archive_suivi_financiers')){
            Schema::table('archive_suivi_financiers', function (Blueprint $table) {
                if (Schema::hasColumn('archive_suivi_financiers', 'archive_suivi_financierable_type') &&
                    Schema::hasColumn('archive_suivi_financiers', 'archive_suivi_financierable_id')) {

                    // Make both morph columns nullable
                    $table->string('archive_suivi_financierable_type')->nullable()->change();
                    $table->unsignedBigInteger('archive_suivi_financierable_id')->nullable()->change();
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
        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                if(Schema::hasColumn('composantes', 'poids')){
                    $table->float('poids')->default(0)->change();
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'poids')){
                    $table->float('poids')->default(0)->change();
                }
            });
        }

        if(Schema::hasTable('taches')){
            Schema::table('taches', function (Blueprint $table) {
                if(Schema::hasColumn('taches', 'poids')){
                    $table->float('poids')->default(0)->change();
                }
            });
        }

        if(Schema::hasTable('archive_composantes')){
            Schema::table('archive_composantes', function (Blueprint $table) {
                if(Schema::hasColumn('archive_composantes', 'poids')){
                    $table->float('poids')->default(0)->change();
                }
            });
        }

        if(Schema::hasTable('archive_activites')){
            Schema::table('archive_activites', function (Blueprint $table) {
                if(Schema::hasColumn('archive_activites', 'poids')){
                    $table->float('poids')->default(0)->change();
                }
            });
        }

        if(Schema::hasTable('archive_taches')){
            Schema::table('archive_taches', function (Blueprint $table) {
                if(Schema::hasColumn('archive_taches', 'poids')){
                    $table->float('poids')->default(0)->change();
                }
            });
        }


        if(Schema::hasTable('suivi_financiers')){
            Schema::table('suivi_financiers', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_financiers', 'suivi_financierable_type') &&
                    Schema::hasColumn('suivi_financiers', 'suivi_financierable_id')) {

                    // Make both morph columns nullable
                    $table->string('suivi_financierable_type')->nullable(false)->change();
                    $table->unsignedBigInteger('suivi_financierable_id')->nullable(false)->change();
                }

            });
        }

        if(Schema::hasTable('archive_suivi_financiers')){
            Schema::table('archive_suivi_financiers', function (Blueprint $table) {
                if (Schema::hasColumn('archive_suivi_financiers', 'archive_suivi_financierable_type') &&
                    Schema::hasColumn('archive_suivi_financiers', 'archive_suivi_financierable_id')) {

                    // Make both morph columns nullable
                    $table->string('archive_suivi_financierable_type')->nullable(false)->change();
                    $table->unsignedBigInteger('archive_suivi_financierable_id')->nullable(false)->change();
                }
            });
        }
    }
}
