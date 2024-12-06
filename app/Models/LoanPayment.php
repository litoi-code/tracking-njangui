<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'amount',
        'principal_amount',
        'interest_amount',
        'remaining_balance',
        'due_date',
        'paid_date',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now()
        ]);
    }

    public function updateStatus()
    {
        if ($this->status !== 'paid' && $this->due_date < now()) {
            $this->update(['status' => 'overdue']);
        }
    }
}
