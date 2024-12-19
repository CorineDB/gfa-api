<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnNomFromCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('categories')){
            
            Schema::table('categories', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('categories', 'nom')){
                    // Query to fetch the unique constraint name for the 'nom' column
                    $uniqueKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'categories' 
                        AND COLUMN_NAME = 'nom'
                    "));
                
                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {
                            // Drop the unique constraint
                            $table->dropUnique("$uniqueConstraintName");
                            //$table->dropUnique("categories_nom_unique");
                            //$table->dropUnique(['nom']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        } 
                    }else {
                        // Fallback: Drop unique constraint using column name
                        $table->dropUnique(['nom']);
                    }
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
        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('categories', 'nom')){
                    // Re-add the unique constraint on the 'nom' column if needed
                        $table->unique('nom');
                    
                }
            });
        }
    }
}
