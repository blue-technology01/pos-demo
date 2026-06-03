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
        Schema::create('product_uoms', function (Blueprint $table) {
            $table->id();

            $table->string('product_code', 20);
            $table->string('uom_code', 20);

            $table->decimal('quantity_per_unit', 10, 2)->default(1);

            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);

            $table->string('barcode', 100)->nullable();
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index('product_code');
            $table->index('uom_code');

            $table->unique(['product_code', 'uom_code']);

            $table->foreign('product_code')
                ->references('code')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('uom_code')
                ->references('code')
                ->on('uoms')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_uoms');
    }
};
