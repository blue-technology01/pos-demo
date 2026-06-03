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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->decimal('opening_balance',10,2)->default(0.00);
            $table->decimal('closing_balance',10,2)->default(0.00);
            $table->decimal('expected_balance',10,2)->default(0.00);
            $table->decimal('difference_amount',10,2)->default(0.00);
            $table->decimal('total_sales', 10, 2)->default(0.00);

            $table->integer('total_transactions')->default(0);

            $table->text('note')->nullable();
            // status
            $table->enum('status', ['open', 'closed'])->default('open');
            // time
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            // make index  for query
            $table->index('status', 'idx_register_status');

            $table->foreign('user_id', 'fk_register_user')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
