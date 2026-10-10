<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->cascadeOnDelete();
            $table->foreignId('employer_id')->nullable()->constrained('employers')->cascadeOnDelete();
            $table->string('code', 10)->index();
            $table->string('name', 150);
            $table->string('type', 20)->default('REMUNERATIVO')->index(); // REMUNERATIVO, NO_REMUNERATIVO, DESCUENTO
            $table->string('formula', 255)->default('SB');
            $table->string('calculation_mode', 20)->default('FORMULA'); // FORMULA, INFORMED_VALUE
            $table->boolean('print_on_receipt')->default(true);
            $table->string('duration_type', 10)->default('T'); // T: Todos los meses
            $table->string('arca_lsd_code', 10)->nullable()->index(); // Código oficial ARCA LSD (ej 110000, 810001)
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();

            $table->index(['agreement_id', 'code']);
            $table->index(['employer_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_concepts');
    }
};
