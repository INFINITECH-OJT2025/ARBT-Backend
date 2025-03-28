<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('image'); // Store image URL
            $table->longText('content'); // Full news article content
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('news');
    }
};
