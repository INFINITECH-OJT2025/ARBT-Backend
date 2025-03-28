    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateTeamsTable extends Migration
    {
        public function up()
        {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role');
                $table->text('description');
                $table->string('image_url')->nullable(); // For storing the image URL
                $table->enum('status', ['Active', 'Archived'])->default('Active');
                $table->timestamps();
            });
        }

        public function down()
        {
            Schema::dropIfExists('teams');
        }
    }