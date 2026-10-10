<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_concepts', function (Blueprint $table) {
            $table->decimal('default_amount', 12, 4)->nullable()->after('formula');
            $table->string('liquidation_condition', 255)->nullable()->after('default_amount');
            $table->string('condition_type', 10)->default('P')->after('liquidation_condition'); // P: Porcentual, U: Unidades/Días, T: Todos
            $table->boolean('print_quantity')->default(true)->after('condition_type');
            $table->boolean('print_unit_price')->default(false)->after('print_quantity');
            $table->boolean('is_automatic')->default(false)->after('print_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_concepts', function (Blueprint $table) {
            $table->dropColumn([
                'default_amount',
                'liquidation_condition',
                'condition_type',
                'print_quantity',
                'print_unit_price',
                'is_automatic',
            ]);
        });
    }
};
