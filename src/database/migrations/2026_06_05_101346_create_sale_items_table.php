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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_id');
            $table->string('product_code', 20);
            $table->string('uom_code', 20)->nullable();

            $table->string('product_name', 150);

            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);

            $table->decimal('discount_percentage', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);

            $table->decimal('amount', 10, 2)->default(0);

            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('product_code')
                ->references('code')
                ->on('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('uom_code')
                ->references('code')
                ->on('uoms')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('sale_id');
            $table->index('product_code');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
