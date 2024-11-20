<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAddressToSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('sites')){
            Schema::table('sites', function (Blueprint $table) {

                if(!Schema::hasColumn('sites', 'quartier')){
                    $table->string('quartier')->default('Sènadé');
                }

                if(!Schema::hasColumn('sites', 'arrondissement')){
                    $table->string('arrondissement')->default('Cotonou');
                }

                if(!Schema::hasColumn('sites', 'commune')){
                    $table->string('commune')->default('Cotonou');
                }

                if(!Schema::hasColumn('sites', 'departement')){
                    $table->string('departement')->default('Litoral');
                }

                if(!Schema::hasColumn('sites', 'pays')){
                    $table->string('pays')->default('Bénin');
                }

                if(!Schema::hasColumn('sites', 'programmeId')){
                    $table->bigInteger('programmeId')->unsigned();
                    $table->foreign('programmeId', 'id')->references('id')->on('programmes')
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
        if(Schema::hasTable('sites')){
            Schema::table('sites', function (Blueprint $table) {

                if(Schema::hasColumn('sites', 'quartier')){
                    $table->dropColumn('quartier');
                }

                if(Schema::hasColumn('sites', 'arrondissement')){
                    $table->dropColumn('arrondissement');
                }

                if(Schema::hasColumn('sites', 'commune')){
                    $table->dropColumn('commune');
                }

                if(Schema::hasColumn('sites', 'departement')){
                    $table->dropColumn('departement');
                }

                if(Schema::hasColumn('sites', 'pays')){
                    $table->dropColumn('pays');
                }

                if(Schema::hasColumn('sites', 'programmeId')){
                    $table->dropForeign(['programmeId']);
                    $table->dropColumn('programmeId');
                }

            });
        }
    }
}
