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
                // Drop 'type' column if it exists
                if (Schema::hasColumn('organisations', 'type')) {
                    $table->dropColumn('type');
                }
            });

            // Recreate the 'type' column with new enum values
            Schema::table('organisations', function (Blueprint $table) {
                $table->enum('type', [
                    'osc_partenaire',
                    'osc_fosir',
                    'autre_osc',
                    'acteurs',
                    'structure_etatique'
                ])->default('osc_partenaire');
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
