<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method'); // e.g., "Cash on Delivery", "GCash", "Credit Card"
            $table->string('status')->default('Pending'); // "Pending", "Completed", "Failed"
            $table->string('payment_proof')->nullable(); // File path for uploaded proof
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('payments');
    }
};