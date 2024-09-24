<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeIdFromAnosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anos', function (Blueprint $table) {
            if (Schema::hasColumn('anos', 'typeId')) {
                $table->dropForeign(['typeId']); // Adjust the column name if necessary
                /*
                $table->dropForeign('anos_typeId_foreign');
                $table->foreign('typeId')->references('id')->on('type_anos')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');*/
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('anos', function (Blueprint $table) {
            
            if (Schema::hasColumn('anos', 'typeId')) {
                $table->dropForeign('anos_typeId_foreign');
                $table->foreign('typeId')->references('id')->on('type_anos')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
            }
        });
    }
}
