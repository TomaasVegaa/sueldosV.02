<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->index();
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::create('agreement_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->cascadeOnDelete();
            $table->string('code', 10)->index();
            $table->string('name', 100);
            $table->string('condition', 10)->default('M'); // M: Mensual, D: Diario, H: Por Hora
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();

            $table->unique(['agreement_id', 'code']);
        });

        Schema::create('category_scale_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_category_id')->constrained('agreement_categories')->cascadeOnDelete();
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable()->index();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('non_taxable_salary', 15, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agreement_category_id', 'effective_from'], 'idx_cat_scale_eff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_scale_versions');
        Schema::dropIfExists('agreement_categories');
        Schema::dropIfExists('agreements');
    }
};
