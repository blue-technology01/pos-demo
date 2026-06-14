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
        Schema::table('sales', function (Blueprint $table) {
            // ✅ Generated column — stores DATE(sale_date) physically
            $table->date('sale_date_only')
                ->virtualAs('DATE(sale_date)')
                ->nullable()
                ->after('sale_date');

            // ✅ Index on the generated column + status
            $table->index(['status', 'sale_date_only'], 'sales_status_sale_date_only_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_status_sale_date_only_index');
            $table->dropColumn('sale_date_only');
        });
    }
};
