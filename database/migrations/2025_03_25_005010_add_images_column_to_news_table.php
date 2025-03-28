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
        Schema::table('news', function (Blueprint $table) {
            // Change the `image` column to TEXT to store JSON-encoded data
            $table->text('image')->nullable()->change(); // Alter the `image` column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            // Revert the column back to TEXT
            $table->text('image')->nullable()->change(); // Change `image` back to TEXT
        });
    }
};
