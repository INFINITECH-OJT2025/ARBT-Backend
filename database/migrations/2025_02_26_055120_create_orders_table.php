<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // ✅ Only create the table if it doesn't exist
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->decimal('total', 10, 2);
                $table->string('payment_method');
                $table->string('status')->default('Pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // ✅ Only drop the table if it exists
        if (Schema::hasTable('orders')) {
            Schema::dropIfExists('orders');
        }
    }
};
