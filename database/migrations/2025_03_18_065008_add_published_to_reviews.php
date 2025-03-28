<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the `published` column doesn't exist in the `reviews` table
        if (!Schema::hasColumn('reviews', 'published')) {
            Schema::table('reviews', function (Blueprint $table) {
                // Only add the `published` column if it does not already exist
                $table->tinyInteger('published')->default(0); // Add the 'published' column
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
        // You can leave the 'down' method empty if you do not want to drop the column
        // because you're not dropping the `published` column.
    }
};
