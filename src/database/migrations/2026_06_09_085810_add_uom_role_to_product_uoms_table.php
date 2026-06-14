<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_uoms', function (Blueprint $table) {
            $table->enum('uom_role', ['retail', 'bulk', 'alternative'])
                  ->default('retail')
                  ->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('product_uoms', function (Blueprint $table) {
            $table->dropColumn('uom_role');
        });
    }
};
