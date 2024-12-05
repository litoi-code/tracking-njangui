<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $accountTypes = AccountType::all();

        // Create some sample accounts
        $accounts = [
            [
                'name' => 'Main Savings',
                'balance' => 500000,
                'currency' => 'XAF',
                'description' => 'Primary savings account',
                'account_type_id' => $accountTypes->where('name', 'Savings Account')->first()->id,
            ],
            [
                'name' => 'Emergency Fund',
                'balance' => 1000000,
                'currency' => 'XAF',
                'description' => 'Emergency savings fund',
                'account_type_id' => $accountTypes->where('name', 'Savings Account')->first()->id,
            ],
            [
                'name' => 'Daily Expenses',
                'balance' => 250000,
                'currency' => 'XAF',
                'description' => 'Account for daily transactions',
                'account_type_id' => $accountTypes->where('name', 'Checking Account')->first()->id,
            ],
            [
                'name' => 'Business Account',
                'balance' => 2000000,
                'currency' => 'XAF',
                'description' => 'Business operations account',
                'account_type_id' => $accountTypes->where('name', 'Business Account')->first()->id,
            ],
        ];

        foreach ($accounts as $accountData) {
            $accountData['user_id'] = $user->id;
            Account::create($accountData);
        }
    }
}
