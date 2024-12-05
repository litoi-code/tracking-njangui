<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransferSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();
        $users = User::all();
        
        if ($accounts->isEmpty() || $users->isEmpty()) {
            return;
        }

        // Create 20 random transfers with past dates
        for ($i = 0; $i < 20; $i++) {
            $fromAccount = $accounts->random();
            $toAccount = $accounts->where('id', '!=', $fromAccount->id)->random();
            $amount = rand(100, 1000);
            $date = Carbon::now()->subMonths(rand(0, 6))->subDays(rand(0, 30));
            $user = $users->random();
            
            // Create transfer
            Transfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'description' => 'Sample transfer #' . ($i + 1),
                'status' => 'completed',
                'executed_at' => $date,
                'user_id' => $user->id
            ]);

            // Update account balances
            $fromAccount->decrement('balance', $amount);
            $toAccount->increment('balance', $amount);
        }
    }
}
