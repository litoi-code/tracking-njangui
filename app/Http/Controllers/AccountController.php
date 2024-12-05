<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with(['accountType', 'user'])
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $accountTypes = AccountType::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CNY'];

        return view('accounts.create', compact('accountTypes', 'users', 'currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'balance' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000'
        ]);

        // Add XAF currency and get first user (temporary)
        $validated['currency'] = 'XAF';
        $validated['user_id'] = User::first()->id;

        $account = Account::create($validated);

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Account created successfully.');
    }

    public function show(Account $account)
    {
        $account->load(['accountType', 'user']);
        
        $transferHistory = $account->getTransferHistory(3);
        $monthlyVolume = $account->getMonthlyTransferVolume();

        // Get monthly balance trend
        $balanceTrend = DB::table('transfers')
            ->select(
                DB::raw('DATE_FORMAT(executed_at, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN from_account_id = ' . $account->id . ' THEN -amount ELSE 0 END) + 
                        SUM(CASE WHEN to_account_id = ' . $account->id . ' THEN amount ELSE 0 END) as net_change')
            )
            ->where(function ($query) use ($account) {
                $query->where('from_account_id', $account->id)
                    ->orWhere('to_account_id', $account->id);
            })
            ->where('executed_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('accounts.show', compact(
            'account',
            'transferHistory',
            'monthlyVolume',
            'balanceTrend'
        ));
    }

    public function edit(Account $account)
    {
        $accountTypes = AccountType::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CNY'];

        return view('accounts.edit', compact('account', 'accountTypes', 'users', 'currencies'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'balance' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000'
        ]);

        // Keep the existing user_id and currency
        $validated['user_id'] = $account->user_id;
        $validated['currency'] = 'XAF';

        $account->update($validated);

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        // Check if account has any transfers
        $hasTransfers = $account->outgoingTransfers()->exists() || 
                       $account->incomingTransfers()->exists();

        if ($hasTransfers) {
            return back()->with('error', 'Cannot delete account with existing transfers.');
        }

        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
