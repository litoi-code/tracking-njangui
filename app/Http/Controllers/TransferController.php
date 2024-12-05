<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = Transfer::with(['fromAccount', 'toAccount', 'user'])
            ->orderBy('executed_at', 'desc')
            ->paginate(15);

        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $accounts = Account::orderBy('name')->get();
        return view('transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => [
                'required',
                'exists:accounts,id',
                'different:from_account_id'
            ],
            'amount' => 'required|numeric|min:0.01',
            'executed_at' => 'required|date',
            'description' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($validated) {
            // Create transfer record
            $transfer = Transfer::create([
                'from_account_id' => $validated['from_account_id'],
                'to_account_id' => $validated['to_account_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'executed_at' => $validated['executed_at'],
                'status' => 'completed',
                'user_id' => User::first()->id // Temporary: Use authenticated user in future
            ]);

            // Update account balances
            $fromAccount = Account::findOrFail($validated['from_account_id']);
            $toAccount = Account::findOrFail($validated['to_account_id']);

            $fromAccount->adjustBalance(-$validated['amount']);
            $toAccount->adjustBalance($validated['amount']);
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Transfer created successfully.');
    }

    public function show(Transfer $transfer)
    {
        return view('transfers.show', compact('transfer'));
    }

    public function bulk()
    {
        $accounts = Account::orderBy('name')->get();
        return view('transfers.bulk', compact('accounts'));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'transfers' => 'required|array|min:1',
            'transfers.*.from_account_id' => 'required|exists:accounts,id',
            'transfers.*.to_account_id' => [
                'required',
                'exists:accounts,id',
                'different:transfers.*.from_account_id'
            ],
            'transfers.*.amount' => 'required|numeric|min:0.01',
            'executed_at' => 'required|date',
            'description' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['transfers'] as $transfer) {
                // Create transfer
                Transfer::create([
                    'from_account_id' => $transfer['from_account_id'],
                    'to_account_id' => $transfer['to_account_id'],
                    'amount' => $transfer['amount'],
                    'description' => $validated['description'] ?? null,
                    'executed_at' => $validated['executed_at'],
                    'status' => 'completed',
                    'user_id' => User::first()->id // Temporary: Use authenticated user in future
                ]);

                // Update balances
                $fromAccount = Account::findOrFail($transfer['from_account_id']);
                $toAccount = Account::findOrFail($transfer['to_account_id']);

                $fromAccount->adjustBalance(-$transfer['amount']);
                $toAccount->adjustBalance($transfer['amount']);
            }
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Bulk transfers created successfully.');
    }

    public function distribute()
    {
        // Get all accounts for source selection
        $sourceAccounts = Account::orderBy('name')->get();
        
        // Get only savings accounts for destination selection
        $destinationAccounts = Account::whereHas('accountType', function($query) {
            $query->where('name', 'Savings Account');
        })->orderBy('name')->get();

        return view('transfers.distribute', compact('sourceAccounts', 'destinationAccounts'));
    }

    public function distributeStore(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'executed_at' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'distributions' => 'required|array|min:1',
            'distributions.*.to_account_id' => [
                'required',
                'exists:accounts,id',
                'different:from_account_id'
            ],
            'distributions.*.amount' => 'required|numeric|min:0.01'
        ]);

        DB::transaction(function () use ($validated) {
            $sourceAccount = Account::findOrFail($validated['from_account_id']);
            
            foreach ($validated['distributions'] as $distribution) {
                // Create transfer
                Transfer::create([
                    'from_account_id' => $sourceAccount->id,
                    'to_account_id' => $distribution['to_account_id'],
                    'amount' => $distribution['amount'],
                    'description' => $validated['description'] ?? null,
                    'executed_at' => $validated['executed_at'],
                    'status' => 'completed',
                    'user_id' => User::first()->id // Temporary: Use authenticated user in future
                ]);

                // Update balances
                $sourceAccount->adjustBalance(-$distribution['amount']);
                Account::findOrFail($distribution['to_account_id'])
                    ->adjustBalance($distribution['amount']);
            }
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Distributions created successfully.');
    }

    public function destroy(Transfer $transfer)
    {
        if ($transfer->status === 'completed') {
            return back()->with('error', 'Cannot delete a completed transfer.');
        }

        $transfer->delete();

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }

    public function getRecentTransfers()
    {
        $transfers = Transfer::with(['fromAccount', 'toAccount', 'user'])
            ->orderBy('executed_at', 'desc')
            ->take(5)
            ->get();
        return response()->json($transfers);
    }
}
