<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::create([
            'name' => 'Caisse Secours',
            'balance' => 0,
            'account_type_id' => 1,
            'user_id' => 1,
        ]);

        Account::create([
            'name' => 'Caisse Développement',
            'balance' => 0,
            'account_type_id' => 1,
            'user_id' => 1,
        ]);

        Account::create([
            'name' => 'Caisse Epargnes',
            'balance' => 0,
            'account_type_id' => 1,
            'user_id' => 1,
        ]);
        Account::create([
            'name' => 'Crédit Scolaire',
            'balance' => 0,
            'account_type_id' => 1,
            'user_id' => 1,
        ]);
        Account::create([
            'name' => 'Ration',
            'balance' => 0,
            'account_type_id' => 1,
            'user_id' => 1,
        ]);
    }
}
