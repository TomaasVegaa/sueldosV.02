<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('cuit', 11)->unique()->index();
            $table->string('legal_name', 200)->index();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });

        Schema::create('employer_settings_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable()->index();
            $table->string('employer_type_code', 10)->nullable();
            $table->string('zone_code', 10)->nullable();
            $table->string('activity_code', 10)->nullable();
            $table->string('payment_place', 150)->nullable();
            $table->string('contribution_bank', 150)->nullable();
            $table->unsignedTinyInteger('receipt_template')->default(1);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('workplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name', 150);
            $table->string('locality', 100);
            $table->string('address', 255)->nullable();
            $table->timestamps();

            $table->unique(['employer_id', 'code']);
        });

        // Add foreign key constraint to user_employer_roles if employers table exists
        Schema::table('user_employer_roles', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_employer_roles', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
        });
        Schema::dropIfExists('workplaces');
        Schema::dropIfExists('employer_settings_versions');
        Schema::dropIfExists('employers');
    }
};
