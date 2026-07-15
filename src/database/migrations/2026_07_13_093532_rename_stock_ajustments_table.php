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
        Schema::rename('stock_ajustments', 'stock_adjustments');
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::rename('stock_adjustments', 'stock_ajustments');
    }
};
