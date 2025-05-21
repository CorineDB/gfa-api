<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditEnumColumnTypeValuesOfOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('organisations')){
            Schema::table('organisations', function (Blueprint $table) {
                if(Schema::hasColumn('organisations', 'type')){
                    $table->enum('type', ['osc_partenaire', 'osc_fosir', 'autre_osc', 'acteurs', 'structure_etatique'])->default('osc_partenaire')->change();
                }
                else{
                    $table->enum('type', ['osc_partenaire', 'osc_fosir', 'autre_osc', 'acteurs', 'structure_etatique'])->default('osc_partenaire');
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
        //
    }
}
