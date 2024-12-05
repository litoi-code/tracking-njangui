<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // Display a listing of transactions
    public function index()
    {
        $transactions = Transaction::with(['fromAccount', 'toAccount'])->get();
        return view('admin.transactions.index', compact('transactions'));
    }

    // Show the form for creating a new transaction
    public function create()
    {
        $accounts = Account::all();
        return view('admin.transactions.create', compact('accounts'));
    }

    // Store a newly created transaction in storage
    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
        ]);

        // Create the transaction
        Transaction::create($request->all());

        // Update account balances
        $fromAccount = Account::find($request->from_account_id);
        $toAccount = Account::find($request->to_account_id);

        $fromAccount->balance -= $request->amount;
        $toAccount->balance += $request->amount;

        $fromAccount->save();
        $toAccount->save();

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    // Show the form for editing the specified transaction
    public function edit(Transaction $transaction)
    {
        $accounts = Account::all();
        return view('admin.transactions.edit', compact('transaction', 'accounts'));
    }

    // Update the specified transaction in storage
    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
        ]);

        // Update the transaction
        $transaction->update($request->all());

        // Update account balances (if necessary)
        // Note: You may want to implement logic to handle balance adjustments here

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    // Remove the specified transaction from storage
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
