<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndUsersSeeder::class,
            ArcaCatalogsSeeder::class,
            AgreementsAndScalesSeeder::class,
            EmployersAndWorkforceSeeder::class,
            GlobalConceptsSeeder::class,
        ]);
    }
}
