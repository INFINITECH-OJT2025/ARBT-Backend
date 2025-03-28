<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration {
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('item');
            $table->decimal('price', 10, 2);
            $table->date('date');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
