<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Períodos de liquidación por empleador
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->unsignedInteger('year_month'); // e.g. 202609
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->timestamps();

            $table->unique(['employer_id', 'year_month'], 'uq_employer_year_month');
        });

        // 2. Lotes o corridas de liquidación (Mensual, Quincenal, SAC, etc.)
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->string('run_type', 10)->default('M'); // M: Mensual, Q1: 1ra Quincena, Q2: 2da Quincena, SAC1, SAC2, VAC, FIN
            $table->unsignedSmallInteger('run_number')->default(1);
            $table->date('pay_date');
            $table->unsignedSmallInteger('base_days')->default(30);
            $table->enum('status', ['DRAFT', 'CALCULATED', 'IN_REVIEW', 'APPROVED', 'CLOSED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employer_id', 'payroll_period_id', 'run_type', 'run_number'], 'uq_employer_run');
        });

        // 3. Versiones inmutables de la corrida (v1, v2 para rectificativas)
        Schema::create('payroll_run_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number')->default(1);
            $table->enum('status', ['CURRENT', 'REPLACED_BY_CORRECTION'])->default('CURRENT');
            $table->foreignId('parent_version_id')->nullable()->constrained('payroll_run_versions')->nullOnDelete();
            $table->text('correction_reason')->nullable();
            $table->char('snapshot_sha256', 64)->nullable();
            $table->longText('snapshot_json')->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'version_number'], 'uq_run_version');
        });

        // 4. Recibos individuales por trabajador en la versión de la corrida
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_version_id')->constrained('payroll_run_versions')->cascadeOnDelete();
            $table->foreignId('employment_id')->constrained('employments')->cascadeOnDelete();
            $table->string('file_number', 20);
            $table->string('worker_name', 150);
            $table->string('worker_cuil', 15);
            $table->string('category_code', 20);
            $table->string('category_name', 100);
            $table->date('hire_date');
            $table->unsignedSmallInteger('seniority_years')->default(0);
            $table->string('health_insurer_code', 10)->nullable();
            $table->string('cbu', 30)->nullable();

            // Totales monetarios (DECIMAL exacto)
            $table->decimal('gross_remunerative', 15, 2)->default(0.00);
            $table->decimal('gross_non_remunerative', 15, 2)->default(0.00);
            $table->decimal('total_deductions', 15, 2)->default(0.00);
            $table->decimal('net_salary', 15, 2)->default(0.00);
            $table->string('net_salary_words', 255)->default('');
            $table->decimal('employer_contributions_total', 15, 2)->default(0.00);

            $table->timestamps();

            $table->unique(['payroll_run_version_id', 'employment_id'], 'uq_payslip_version_emp');
        });

        // 5. Líneas individuales de conceptos liquidados en el recibo
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payslips')->cascadeOnDelete();
            $table->foreignId('payroll_concept_id')->nullable()->constrained('payroll_concepts')->nullOnDelete();
            $table->string('concept_code', 10);
            $table->string('concept_name', 120);
            $table->enum('concept_type', ['REMUNERATIVO', 'NO_REMUNERATIVO', 'DESCUENTO']);
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->decimal('remunerative_amount', 15, 2)->default(0.00);
            $table->decimal('non_taxable_amount', 15, 2)->default(0.00);
            $table->decimal('deduction_amount', 15, 2)->default(0.00);
            $table->string('arca_lsd_code', 10)->nullable();
            $table->unsignedSmallInteger('order')->default(500);
            $table->boolean('print_on_receipt')->default(true);
            $table->text('calculation_trace')->nullable();
            $table->timestamps();

            $table->index(['payslip_id', 'order']);
        });

        // 6. Bases imponibles ARCA acumuladas por recibo (Base 1 a Base 10)
        Schema::create('payslip_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payslips')->cascadeOnDelete();
            $table->string('base_code', 20); // BASE_1, BASE_2, BASE_4, etc.
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['payslip_id', 'base_code'], 'uq_payslip_base');
        });

        // 7. Novedades / Valores informados por empleado para una corrida
        Schema::create('payroll_employee_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->foreignId('employment_id')->constrained('employments')->cascadeOnDelete();
            $table->unsignedInteger('year_month'); // e.g. 202609
            $table->unsignedSmallInteger('worked_days')->default(30);
            $table->unsignedSmallInteger('absence_days')->default(0);
            $table->decimal('overtime_50_hours', 8, 2)->default(0.00);
            $table->decimal('overtime_100_hours', 8, 2)->default(0.00);
            $table->json('custom_informed_values')->nullable(); // {'VI_004': 0, 'VI_101': 50000}
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employer_id', 'employment_id', 'year_month'], 'uq_emp_input_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_inputs');
        Schema::dropIfExists('payslip_bases');
        Schema::dropIfExists('payslip_lines');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_run_versions');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('payroll_periods');
    }
};
