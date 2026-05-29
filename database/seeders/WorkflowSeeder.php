<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AgentTeamSeeder::class,
        ]);
    }
}
