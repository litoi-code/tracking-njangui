<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Savings Account',
                'description' => 'Personal savings account for long-term savings'
            ],
            [
                'name' => 'Checking Account',
                'description' => 'Regular checking account for daily transactions'
            ],
            // [
            //     'name' => 'Investment Account',
            //     'description' => 'Account for investment purposes'
            // ],
            // [
            //     'name' => 'Business Account',
            //     'description' => 'Account for business transactions'
            // ],
            // [
            //     'name' => 'Joint Account',
            //     'description' => 'Shared account between multiple users'
            // ]
        ];

        foreach ($types as $type) {
            AccountType::create($type);
        }
    }
}
