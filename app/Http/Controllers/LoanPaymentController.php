<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;

class LoanPaymentController extends Controller
{
    public function index(Loan $loan)
    {
        $payments = $loan->payments()
            ->orderBy('due_date')
            ->get();

        return view('loans.payments.index', compact('loan', 'payments'));
    }

    public function makePayment(Request $request, Loan $loan, LoanPayment $payment)
    {
        try {
            $loan->makePayment($payment->id);
            return redirect()
                ->route('loans.payments.index', $loan)
                ->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
