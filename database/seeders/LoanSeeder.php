<?php

namespace Database\Seeders;

use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        // Example 1: Small loan (100,000 XAF for 6 months at 5%)
        $loan1 = new Loan([
            'account_id' => 1,
            'amount' => 100000,
            'interest_rate' => 5, // 5%
            'term_months' => 6,
            'status' => 'active',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(6),
            'next_payment_date' => Carbon::now()->addMonth(),
        ]);
        $loan1->monthly_payment = $loan1->calculateMonthlyPayment(); // ≈ 17,125 XAF
        $loan1->remaining_balance = $loan1->amount;
        $loan1->save();

        // Example 2: Medium loan (500,000 XAF for 12 months at 8%)
        $loan2 = new Loan([
            'account_id' => 2,
            'amount' => 500000,
            'interest_rate' => 8, // 8%
            'term_months' => 12,
            'status' => 'active',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(12),
            'next_payment_date' => Carbon::now()->addMonth(),
        ]);
        $loan2->monthly_payment = $loan2->calculateMonthlyPayment(); // ≈ 43,869 XAF
        $loan2->remaining_balance = $loan2->amount;
        $loan2->save();

        // Example 3: Large loan (1,000,000 XAF for 24 months at 10%)
        $loan3 = new Loan([
            'account_id' => 3,
            'amount' => 1000000,
            'interest_rate' => 10, // 10%
            'term_months' => 24,
            'status' => 'active',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(24),
            'next_payment_date' => Carbon::now()->addMonth(),
        ]);
        $loan3->monthly_payment = $loan3->calculateMonthlyPayment(); // ≈ 46,144 XAF
        $loan3->remaining_balance = $loan3->amount;
        $loan3->save();
    }
}
