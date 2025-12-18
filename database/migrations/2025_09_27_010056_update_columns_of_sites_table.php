<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsOfSitesTable extends Migration
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

                // Latitude et Longitude en DOUBLE pour rapidité et calculs
                if (Schema::hasColumn('sites', 'latitude')) {
                    $table->float('latitude', 10, 8)->nullable()->change();
                } else {
                    $table->float('latitude', 10, 8)->nullable();
                }

                if (Schema::hasColumn('sites', 'longitude')) {
                    $table->float('longitude', 11, 8)->nullable()->change();
                } else {
                    $table->float('longitude', 11, 8)->nullable();
                }

                if(Schema::hasColumn('sites', 'quartier')){
                    $table->longText('quartier')->nullable()->change();
                }

                if(Schema::hasColumn('sites', 'arrondissement')){
                    $table->longText('arrondissement')->nullable()->change();
                }

                if(Schema::hasColumn('sites', 'commune')){
                    $table->longText('commune')->nullable()->change();
                }

                if(Schema::hasColumn('sites', 'departement')){
                    $table->longText('departement')->nullable()->change();
                }

                if(Schema::hasColumn('sites', 'departement')){
                    $table->longText('departement')->nullable()->change();
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
                // Latitude et Longitude en DOUBLE pour rapidité et calculs
                if (Schema::hasColumn('sites', 'latitude')) {
                    $table->float('latitude', 10, 8)->nullable()->change();
                }

                if (Schema::hasColumn('sites', 'longitude')) {
                    $table->float('longitude', 11, 8)->nullable()->change();
                }

                if(Schema::hasColumn('sites', 'quartier')){
                    $table->string('quartier')->nullable()->default('Sènadé')->change();
                }

                if(Schema::hasColumn('sites', 'arrondissement')){
                    $table->string('arrondissement')->nullable()->default('Cotonou')->change();
                }

                if(Schema::hasColumn('sites', 'commune')){
                    $table->string('commune')->nullable()->default('Cotonou')->change();
                }

                if(Schema::hasColumn('sites', 'departement')){
                    $table->string('departement')->nullable()->default('Litoral')->change();
                }
            });
        }
    }
}
