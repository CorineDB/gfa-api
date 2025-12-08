<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPositionToCategoriesDeGouvernanceAndQuestionsDeGouvenanceTable extends Migration
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
                if(!Schema::hasColumn('categories_de_gouvernance', 'position')){
                    $table->integer('position')->default(0);
                }
            });
        }

        if(Schema::hasTable('questions_de_gouvernance')){
            Schema::table('questions_de_gouvernance', function (Blueprint $table) {
                if(!Schema::hasColumn('questions_de_gouvernance', 'position')){
                    $table->integer('position')->default(0);
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
                if(Schema::hasColumn('categories_de_gouvernance', 'position')){
                    $table->dropColumn('position');
                }
            });
        }

        if(Schema::hasTable('questions_de_gouvernance')){
            Schema::table('questions_de_gouvernance', function (Blueprint $table) {
                if(Schema::hasColumn('questions_de_gouvernance', 'position')){
                    $table->dropColumn('position');
                }
            });
        }
    }
}
