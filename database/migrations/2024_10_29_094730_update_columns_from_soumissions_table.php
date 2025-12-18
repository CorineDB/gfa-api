<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsFromSoumissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('soumissions')){
            Schema::table('soumissions', function (Blueprint $table) {
                if(!Schema::hasColumn('soumissions', 'categorieDeParticipant')){
                    $table->enum('categorieDeParticipant', ['membre_de_conseil_administration', 'employe_association', 'membre_association', 'partenaire'])->nullable();
                }
                if(!Schema::hasColumn('soumissions', 'sexe')){
                    $table->string('sexe')->nullable();
                }
                if(!Schema::hasColumn('soumissions', 'age')){
                    $table->string('age')->nullable();
                }
                if(Schema::hasColumn('soumissions', 'submitted_at')){
                    $table->datetime('submitted_at')->nullable()->change();
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
        if(Schema::hasTable('soumissions')){
            Schema::table('soumissions', function (Blueprint $table) {
                if(Schema::hasColumn('soumissions', 'categorieDeParticipant')){
                    $table->dropColumn('categorieDeParticipant');
                }

                if(Schema::hasColumn('soumissions', 'sexe')){
                    $table->dropColumn('sexe');
                }

                if(Schema::hasColumn('soumissions', 'age')){
                    $table->dropColumn('age');
                }
                if(Schema::hasColumn('soumissions', 'submitted_at')){
                    $table->datetime('submitted_at')->nullable(false)->change();
                }
            });
        }
    }
}
