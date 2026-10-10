<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('cuil', 11)->unique()->index();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->index();
            $table->timestamps();
        });

        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->string('file_number', 10)->index(); // Legajo
            $table->date('hire_date');
            $table->date('seniority_date');
            $table->date('termination_date')->nullable()->index();
            $table->string('termination_cause', 50)->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->unique(['employer_id', 'file_number']);
        });

        Schema::create('employment_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable()->index();
            $table->string('agreement_code', 10)->default('003'); // CCT (ej Comercio 003)
            $table->string('category_code', 10);
            $table->string('category_name', 100)->nullable();
            $table->unsignedTinyInteger('workday_type')->default(1); // 1: Completa, 2: 3/4, 3: 1/2, 4: 1/4
            $table->string('situation_code', 10)->default('01');
            $table->string('contract_mode_code', 10)->default('014');
            $table->string('health_insurer_code', 6)->default('126205'); // OSECAC
            $table->string('activity_code', 10)->default('049');
            $table->string('zone_code', 10)->default('84');
            $table->string('condition_code', 10)->default('01');
            $table->string('employer_type_code', 10)->default('1');
            $table->string('cbu', 22)->nullable();
            $table->decimal('agreed_salary', 15, 2)->nullable();
            $table->timestamps();

            $table->index(['employer_id', 'employment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_versions');
        Schema::dropIfExists('employments');
        Schema::dropIfExists('workers');
    }
};
