<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointFocalColumnsToOrganisationsTable extends Migration
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
                if(!Schema::hasColumn('organisations', 'nom_point_focal')){
                    $table->string('nom_point_focal')->nullable();
                }

                if(!Schema::hasColumn('organisations', 'prenom_point_focal')){
                    $table->string('prenom_point_focal')->nullable();
                }

                if(!Schema::hasColumn('organisations', 'contact_point_focal')){
                    $table->string('contact_point_focal')->nullable();
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
        if(Schema::hasTable('organisations')){
            Schema::table('organisations', function (Blueprint $table) {
                if(Schema::hasColumn('organisations', 'nom_point_focal')){
                    $table->dropColumn(['nom_point_focal']);
                }

                if(Schema::hasColumn('organisations', 'prenom_point_focal')){
                    $table->dropColumn(['prenom_point_focal']);
                }

                if(Schema::hasColumn('organisations', 'contact_point_focal')){
                    $table->dropColumn(['contact_point_focal']);
                }
            });
        }
    }
}
