<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arca_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('catalog_type', 30)->index(); // OBRA_SOCIAL, ACTIVIDAD, MODALIDAD, SIT_REVISTA, CONDICION, ZONA
            $table->string('code', 10)->index();
            $table->string('description', 255);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['catalog_type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arca_catalogs');
    }
};
