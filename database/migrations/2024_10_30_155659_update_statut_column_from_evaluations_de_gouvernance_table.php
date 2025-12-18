<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatutColumnFromEvaluationsDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('evaluations_de_gouvernance')){
            Schema::table('evaluations_de_gouvernance', function (Blueprint $table) {
                if(Schema::hasColumn('evaluations_de_gouvernance', 'statut')){
                    $table->integer('statut')->default(-1)->change();
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
        if(Schema::hasTable('evaluations_de_gouvernance')){
            Schema::table('evaluations_de_gouvernance', function (Blueprint $table) {
                if(Schema::hasColumn('evaluations_de_gouvernance', 'statut')){
                    $table->boolean('statut')->default(0)->change();
                }
            });
        }
    }
}
