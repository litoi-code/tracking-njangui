<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with('account')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $accounts = Account::orderBy('balance', 'desc')->get();
        return view('loans.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($validated) {
            // Create the loan
            $loan = Loan::create($validated);
            
            // Generate payment schedule
            $loan->generatePaymentSchedule();
            
            // Update account balances
            $loan->fromAccount->increment('balance', $validated['amount']);
            $loan->toAccount->decrement('balance', $validated['amount']);
        });

        return redirect()->route('loans.index')
            ->with('success', 'Prêt créé avec succès.');
    }

    public function show(Loan $loan)
    {
        return view('loans.show', compact('loan'));
    }

    public function makePayment(Loan $loan)
    {
        if ($loan->status !== 'active') {
            return back()->with('error', 'Can only make payments on active loans');
        }

        DB::transaction(function () use ($loan) {
            // Calculate interest and principal portions
            $monthlyRate = $loan->interest_rate / 12 / 100;
            $interest = $loan->remaining_balance * $monthlyRate;
            $principal = $loan->monthly_payment - $interest;

            // Create transfer for payment
            Transfer::create([
                'from_account_id' => $loan->account_id,
                'to_account_id' => 1, // System account or main cash account
                'amount' => $loan->monthly_payment,
                'description' => "Loan payment #" . $loan->id . " (Principal: " . number_format($principal, 2) . " XAF, Interest: " . number_format($interest, 2) . " XAF)",
                'status' => 'completed'
            ]);

            // Update loan
            $loan->remaining_balance -= $principal;
            $loan->next_payment_date = Carbon::parse($loan->next_payment_date)->addMonth();
            
            if ($loan->remaining_balance <= 0) {
                $loan->status = 'completed';
                $loan->remaining_balance = 0;
            }
            
            $loan->save();

            // Update account balance
            Account::where('id', $loan->account_id)
                ->decrement('balance', $loan->monthly_payment);
        });

        return back()->with('success', 'Payment processed successfully');
    }

    public function calculateAmortization(Loan $loan)
    {
        $schedule = [];
        $balance = $loan->amount;
        $payment = $loan->monthly_payment;
        $monthlyRate = $loan->interest_rate / 12 / 100;

        for ($month = 1; $month <= $loan->term_months; $month++) {
            $interest = $balance * $monthlyRate;
            $principal = $payment - $interest;
            $balance -= $principal;

            $schedule[] = [
                'month' => $month,
                'payment' => $payment,
                'principal' => $principal,
                'interest' => $interest,
                'balance' => max(0, $balance)
            ];
        }

        return response()->json($schedule);
    }
}
