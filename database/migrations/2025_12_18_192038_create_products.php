<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic product info
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('category_id')->nullable();

            // Descriptions
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            // Media
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->enum('discount_type', ['flat', 'percent'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();

            // Inventory
            $table->integer('stock')->default(0);

            // Extra fields
            $table->string('sku')->nullable()->unique();
            $table->string('demo_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
