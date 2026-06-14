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
        Schema::create('sales', function (Blueprint $table) {

            $table->id();
            // References
            $table->unsignedBigInteger('register_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            // Invoice
            $table->string('invoice_no', 50)->unique();
            $table->date('sale_date');
            // Amounts
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            // Payment
            $table->enum('payment_method', ['cash', 'card', 'qr'])->default('cash');
            // Status
            $table->enum('status', [ 'completed', 'voided', 'refunded'])->default('completed');
            // Notes
            $table->text('note')->nullable();
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamps();

            $table->index('sale_date');
            $table->index('user_id');
            $table->index('customer_id');
            $table->index('register_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index('created_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('register_id')
                ->references('id')
                ->on('cash_registers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('voided_by')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
