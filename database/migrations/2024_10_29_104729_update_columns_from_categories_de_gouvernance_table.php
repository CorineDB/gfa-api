<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsFromCategoriesDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('categories_de_gouvernance')){
            Schema::table('categories_de_gouvernance', function (Blueprint $table) {
                if(!Schema::hasColumn('categories_de_gouvernance', 'formulaireDeGouvernanceId')){
                    $table->bigInteger('formulaireDeGouvernanceId')->nullable()->unsigned();
                    $table->foreign('formulaireDeGouvernanceId')->references('id')->on('formulaires_de_gouvernance')
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
        if(Schema::hasTable('categories_de_gouvernance')){
            Schema::table('categories_de_gouvernance', function (Blueprint $table) {
                if(Schema::hasColumn('categories_de_gouvernance', 'formulaireDeGouvernanceId')){
                    $table->dropForeign(['formulaireDeGouvernanceId']);
                    $table->dropColumn('formulaireDeGouvernanceId');
                }
            });
        }
    }
}
