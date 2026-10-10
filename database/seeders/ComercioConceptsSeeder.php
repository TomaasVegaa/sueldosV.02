<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Los conceptos de liquidación son globales para todos los convenios (GlobalConceptsSeeder).
 */
class ComercioConceptsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GlobalConceptsSeeder::class);
    }
}
