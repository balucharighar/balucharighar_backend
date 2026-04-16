<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name')->nullable();
            $table->string('city')->nullable();

            // Product Experience
            $table->string('useful');
            $table->text('liked')->nullable();
            $table->text('confused')->nullable();

            // Pricing
            $table->string('pay');
            $table->string('price');

            // Improvements
            $table->text('improvement')->nullable();
            $table->text('feature')->nullable();

            // Rating
            $table->integer('rating');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
