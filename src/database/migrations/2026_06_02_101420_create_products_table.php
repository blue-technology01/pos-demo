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
            $table->string('code',20)->primary();
            $table->string('name', 150 );
            $table->string('category_code',20)-> nullable();
            $table->decimal('cost_price',10,2)->default(0);
            $table->decimal('price',10,2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('min_stock', 10, 2)->default(0);
            $table->string('barcode', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // relationship with category
            $table->foreign('category_code')
                ->references('code')
                ->on('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();

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
