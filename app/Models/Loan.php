<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'amount',
        'interest_rate',
        'term_months',
        'status',
        'description'
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function generatePaymentSchedule()
    {
        $amount = $this->amount;
        $monthlyRate = $this->interest_rate / 100;
        $months = $this->term_months;
        
        // Calculate total interest
        $totalInterest = $amount * $monthlyRate * $months;
        
        // Calculate total amount to be paid
        $totalAmount = $amount + $totalInterest;
        
        // Calculate fixed monthly payment
        $monthlyPayment = $totalAmount / $months;
        
        // Calculate monthly principal payment (equal for all months)
        $monthlyPrincipal = $amount / $months;
        
        // Calculate monthly interest payment (equal for all months)
        $monthlyInterest = $totalInterest / $months;

        $balance = $totalAmount;
        $startDate = Carbon::now();

        for ($month = 1; $month <= $months; $month++) {
            $balance -= $monthlyPayment;

            $this->payments()->create([
                'amount' => round($monthlyPayment, 2),
                'principal_amount' => round($monthlyPrincipal, 2),
                'interest_amount' => round($monthlyInterest, 2),
                'remaining_balance' => round($balance, 2),
                'due_date' => $startDate->copy()->addMonths($month),
                'status' => 'pending'
            ]);
        }
    }

    public function makePayment($paymentId)
    {
        $payment = $this->payments()->findOrFail($paymentId);
        
        if ($payment->status === 'paid') {
            throw new \Exception('Payment has already been made.');
        }

        // Start a database transaction
        \DB::transaction(function () use ($payment) {
            // Deduct from beneficiary account
            $this->toAccount->decrement('balance', $payment->amount);
            
            // Add to source account
            $this->fromAccount->increment('balance', $payment->amount);
            
            // Mark payment as paid
            $payment->markAsPaid();
        });

        return $payment;
    }

    public function updatePaymentStatuses()
    {
        $this->payments()->each(function ($payment) {
            $payment->updateStatus();
        });
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->payments()
                    ->where('status', '!=', 'paid')
                    ->sum('amount');
    }

    public function getNextPaymentAttribute()
    {
        return $this->payments()
                    ->where('status', '!=', 'paid')
                    ->orderBy('due_date')
                    ->first();
    }
}
