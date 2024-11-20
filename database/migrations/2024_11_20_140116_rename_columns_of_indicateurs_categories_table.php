<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsOfIndicateursCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(!Schema::hasColumn('indicateurs', 'indice')){
                    $table->integer('indice');
                }

                if(Schema::hasColumn('indicateurs', 'responsable')){
                    $table->dropColumn('responsable');
                }
            });
        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {

                if(Schema::hasColumn('categories', 'nom')){
                    $table->longText('nom')->change();
                }

                if(!Schema::hasColumn('categories', 'indice')){
                    $table->integer('indice');
                }

                if (!Schema::hasColumn('categories', 'type')) {
                    $table->enum('type', ['impact', 'effet', 'produit'])->default('produit');
                }

                if(!Schema::hasColumn('categories', 'categorieId')){
                    $table->bigInteger('categorieId')->nullable()->unsigned();
                    $table->foreign('categorieId')->references('id')->on('categories')
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
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(Schema::hasColumn('indicateurs', 'indice')){
                    $table->dropColumn('indice');
                }
        
                if(!Schema::hasColumn('indicateurs', 'responsable')){
                    $table->string('responsable');
                }

            });
        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {

                if(Schema::hasColumn('categories', 'indice')){
                    $table->dropColumn('indice');
                }

                if(Schema::hasColumn('indicateurs', 'type')){
                    $table->dropColumn('type');
                }
            });
        }
    }
}
