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
        // For MySQL (if your database is MySQL)
        Schema::table('news', function (Blueprint $table) {
            // This is a raw SQL statement to convert the 'image' column from TEXT to JSON
            DB::statement('ALTER TABLE news MODIFY image JSON');
        });

        // If using PostgreSQL, you can modify the type like so:
        // DB::statement('ALTER TABLE news ALTER COLUMN image TYPE JSON');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to TEXT in case of rollback
        Schema::table('news', function (Blueprint $table) {
            DB::statement('ALTER TABLE news MODIFY image TEXT');
        });
    }
};
