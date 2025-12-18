<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPtabScopesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('ptab_scopes')) {
            Schema::table('ptab_scopes', function (Blueprint $table) {
            
                if (!Schema::hasColumn('ptab_scopes', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned();

                    $table->foreign('programmeId')->references('id')->on('programmes')
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
        if(Schema::hasTable('ptab_scopes')) {
            Schema::table('ptab_scopes', function (Blueprint $table) {
            
                if (Schema::hasColumn('ptab_scopes', 'programmeId')) {
                    
			        $table->dropForeign('ptab_scopes_programmeId_foreign');

                    $table->dropColumn('programmeId');
                }
            });
        }
    }
}
