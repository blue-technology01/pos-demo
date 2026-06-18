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
        // 1 core = 2 threats
        // 7 core = 14 threats

        Schema::create('blocked_sale_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_uom_id')
                ->constrained('product_uoms')
                ->cascadeOnDelete();

            $table->decimal('requested_qty', 10, 2);
            $table->decimal('available_stock', 10, 2);

            $table->string('reason');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['product_uom_id', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('blocked_sale_attempts');
    }
};
