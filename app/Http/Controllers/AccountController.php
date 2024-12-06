<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::with(['accountType', 'user']);

        // Filter by account type if specified
        if ($request->has('type') && $request->type != 'all') {
            $query->whereHas('accountType', function($q) use ($request) {
                $q->where('id', $request->type);
            });
        }

        $accounts = $query->orderBy('balance', 'desc')->paginate(12);
        
        // Get account types with counts
        $accountTypes = AccountType::withCount('accounts')
            ->orderBy('name')
            ->get();

        // Append type parameter to pagination links if it exists
        if ($request->has('type')) {
            $accounts->appends(['type' => $request->type]);
        }

        return view('accounts.index', compact('accounts', 'accountTypes'));
    }

    public function create()
    {
        $accountTypes = AccountType::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CNY'];

        // Get the checking account type ID
        $defaultAccountTypeId = AccountType::where('name', 'like', '%Checking%')->first()?->id;

        return view('accounts.create', compact('accountTypes', 'users', 'currencies', 'defaultAccountTypeId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'balance' => 'required|integer|min:0',
        ]);

        $account = Account::create([
            'name' => $validated['name'],
            'account_type_id' => $validated['account_type_id'],
            'balance' => $validated['balance'],
        ]);

        return redirect()->route('accounts.show', ['account' => $account->id])
            ->with('success', 'Account created successfully with initial balance of ' . number_format($account->balance, 0) . ' XAF');
    }

    public function show(Account $account)
    {
        // Calculate monthly volume for current month
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthlyVolume = [
            'incoming' => Transfer::where('to_account_id', $account->id)
                                ->whereBetween('executed_at', [$monthStart, $monthEnd])
                                ->sum('amount'),
            'outgoing' => Transfer::where('from_account_id', $account->id)
                                ->whereBetween('executed_at', [$monthStart, $monthEnd])
                                ->sum('amount'),
            'incoming_count' => Transfer::where('to_account_id', $account->id)
                                      ->whereBetween('executed_at', [$monthStart, $monthEnd])
                                      ->count(),
            'outgoing_count' => Transfer::where('from_account_id', $account->id)
                                      ->whereBetween('executed_at', [$monthStart, $monthEnd])
                                      ->count()
        ];

        // Initial transfers load
        $transfers = $this->transfers($account);

        return view('accounts.show', compact('account', 'transfers', 'monthlyVolume'));
    }

    public function transfers(Account $account)
    {
        $query = Transfer::where(function($q) use ($account) {
            $q->where('from_account_id', $account->id)
              ->orWhere('to_account_id', $account->id);
        });

        // Account name search
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('fromAccount', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('toAccount', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // Date range filter
        if (request('start_date')) {
            $query->whereDate('executed_at', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $query->whereDate('executed_at', '<=', request('end_date'));
        }

        $transfers = $query->orderBy('executed_at', 'desc')
                          ->orderBy('amount', 'desc')
                          ->with(['fromAccount', 'toAccount'])
                          ->paginate(15)
                          ->withQueryString();

        if (request()->ajax()) {
            return view('accounts._transfers_table', compact('account', 'transfers'))->render();
        }

        return $transfers;
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
            'balance' => 'required|integer|min:0',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.show', ['account' => $account->id])
            ->with('success', 'Account updated successfully. New balance: ' . number_format($account->balance, 0) . ' XAF');
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
