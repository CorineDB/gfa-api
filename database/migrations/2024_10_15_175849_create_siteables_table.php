<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siteables', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('site_id')->unsigned();
            $table->foreign('site_id')->references('id')->on('sites')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->morphs('siteable');
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs', 'indice')){
                    $table->dropColumn(['indice']);
                }
            });
        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {
                if(Schema::hasColumn('categories', 'indice')){
                    $table->dropColumn(['indice']);
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
        Schema::dropIfExists('siteables');
    }
}
