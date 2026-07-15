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
        Schema::create('stock_ajustments', function (Blueprint $table) {
            $table->id();

            $table->string('product_code');
            $table->foreign('product_code')->references('code')->on('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');

            $table->date('adjustment_date');
            $table->unsignedInteger('new_quantity');
            $table->string('reason_code'); // Damage, Break, Other
            $table->text('remark')->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['product_code', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ajustments');
    }
};
