<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('price');
            $table->text('description');
            $table->json('features'); // ✅ Store features as JSON
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('subscription_plans');
    }
};
