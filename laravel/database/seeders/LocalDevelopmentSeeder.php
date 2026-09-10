<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class LocalDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
