<?php

// database/migrations/xxxx_xx_xx_create_news_images_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsImagesTable extends Migration
{
    public function up()
    {
        Schema::create('news_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_id');
            $table->string('image_path');
            $table->timestamps();

            $table->foreign('news_id')
                  ->references('id')
                  ->on('news')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('news_images');
    }
}
