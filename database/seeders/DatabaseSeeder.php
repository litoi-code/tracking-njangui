<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\AccountTypeSeeder;
use Database\Seeders\AccountSeeder;
use Database\Seeders\TransferSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\LoanSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            AccountTypeSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            // TransferSeeder::class,
        ]);
    }
}
