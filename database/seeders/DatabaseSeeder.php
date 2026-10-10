<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ArcaCatalogsSeeder::class,
            RolesAndUsersSeeder::class,
            EmployersAndWorkforceSeeder::class,
            GlobalConceptsSeeder::class,
        ]);
    }
}
