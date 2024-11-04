<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmptyMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reponses_de_la_collecte')->truncate();
        DB::table('soumissions')->truncate();
        DB::table('questions_de_gouvernance')->truncate();
        DB::table('categories_de_gouvernance')->truncate();
        DB::table('formulaire_options_de_reponse')->truncate();
        DB::table('formulaires_de_gouvernance')->truncate();
        DB::table('formulaire_options_de_reponse')->truncate();
        DB::table('evaluation_formulaires_de_gouvernance')->truncate();
        DB::table('evaluation_organisations')->truncate();
        DB::table('evaluations_de_gouvernance')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
