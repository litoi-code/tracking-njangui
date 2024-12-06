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
    public function index(Request $request)
    {
        $query = Transfer::with(['fromAccount', 'toAccount'])
            ->when($request->from_account, function($q) use ($request) {
                return $q->where('from_account_id', $request->from_account);
            })
            ->when($request->to_account, function($q) use ($request) {
                return $q->where('to_account_id', $request->to_account);
            })
            ->when($request->date_from, function($q) use ($request) {
                return $q->whereDate('executed_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function($q) use ($request) {
                return $q->whereDate('executed_at', '<=', $request->date_to);
            })
            ->when($request->min_amount, function($q) use ($request) {
                return $q->where('amount', '>=', $request->min_amount);
            })
            ->when($request->max_amount, function($q) use ($request) {
                return $q->where('amount', '<=', $request->max_amount);
            })
            ->when($request->status, function($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->latest('executed_at');

        // Get today's hourly transfer volumes
        $todayVolumes = Transfer::selectRaw('HOUR(executed_at) as hour, SUM(amount) as total_amount')
            ->whereDate('executed_at', now()->toDateString())
            ->where('status', 'completed')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'amount' => (int) $item->total_amount
                ];
            });

        $accounts = Account::orderBy('name')->get();
        $transfers = $query->paginate(10)->withQueryString();

        return view('transfers.index', [
            'transfers' => $transfers,
            'accounts' => $accounts,
            'filters' => $request->all(),
            'todayVolumes' => $todayVolumes
        ]);
    }

    public function create()
    {
        $accounts = Account::orderBy('balance', 'desc')->get();
        return view('transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0',
            'transfer_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:1000'
        ]);

        $fromAccount = Account::findOrFail($validated['from_account_id']);
        $amount = floatval($validated['amount']);

        try {
            DB::transaction(function () use ($validated, $fromAccount, $amount) {
                // Create the transfer record
                $transfer = Transfer::create([
                    'from_account_id' => $validated['from_account_id'],
                    'to_account_id' => $validated['to_account_id'],
                    'amount' => $amount,
                    'description' => $validated['description'] ?? null,
                    'executed_at' => Carbon::parse($validated['transfer_date']),
                    'status' => 'completed',
                    'user_id' => auth()->id()
                ]);

                // Update account balances
                $fromAccount->decrement('balance', $amount);
                Account::where('id', $validated['to_account_id'])->increment('balance', $amount);
            });

            return redirect()
                ->route('accounts.show', ['account' => $validated['from_account_id']])
                ->with('success', 'Transfer of ' . number_format($amount, 0) . ' FCFA completed successfully');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create transfer. Please try again.']);
        }
    }

    public function show(Transfer $transfer)
    {
        return view('transfers.show', compact('transfer'));
    }

    public function edit(Transfer $transfer)
    {
        $accounts = Account::orderBy('name')->get();
        return view('transfers.edit', compact('transfer', 'accounts'));
    }

    public function update(Request $request, Transfer $transfer)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'executed_at' => 'required|date'
        ]);

        DB::transaction(function () use ($validated, $transfer) {
            // If this is a completed transfer, we need to reverse the previous transaction first
            if ($transfer->status === 'completed') {
                // Reverse the old transfer
                $oldFromAccount = Account::findOrFail($transfer->from_account_id);
                $oldToAccount = Account::findOrFail($transfer->to_account_id);
                $oldFromAccount->increment('balance', $transfer->amount);
                $oldToAccount->decrement('balance', $transfer->amount);
            }

            // Update the transfer record
            $transfer->update([
                'from_account_id' => $validated['from_account_id'],
                'to_account_id' => $validated['to_account_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'executed_at' => $validated['executed_at']
            ]);

            // If this is a completed transfer, apply the new transaction
            if ($transfer->status === 'completed') {
                // Apply the new transfer
                $newFromAccount = Account::findOrFail($validated['from_account_id']);
                $newToAccount = Account::findOrFail($validated['to_account_id']);
                $newFromAccount->decrement('balance', $validated['amount']);
                $newToAccount->increment('balance', $validated['amount']);
            }
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Transfer updated successfully.');
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
        // Get all accounts grouped by type for source account
        $groupedAccounts = Account::with('accountType')
            ->get()
            ->groupBy('accountType.name');

        // Get only savings accounts for destination
        $savingsAccounts = Account::whereHas('accountType', function($query) {
            $query->where('name', 'Savings Account');
        })->orderBy('name')->get();

        return view('transfers.distribute', compact('groupedAccounts', 'savingsAccounts'));
    }

    public function distributeStore(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'amounts' => 'required|array',
            'amounts.*' => 'required|numeric',
            'to_account_ids' => 'required|array|size:'.count($request->input('amounts', [])),
            'to_account_ids.*' => 'required|exists:accounts,id|different:from_account_id',
            'description' => 'nullable|string|max:1000',
            'executed_at' => 'required|date'
        ], [
            'amounts.required' => 'At least one distribution amount is required.',
            'amounts.*.required' => 'All distribution amounts must be specified.',
            'amounts.*.numeric' => 'All amounts must be numbers.',
            'to_account_ids.size' => 'The number of destination accounts must match the number of amounts.',
            'to_account_ids.*.different' => 'Destination account cannot be the same as the source account.',
            'executed_at.required' => 'The execution date is required.',
            'executed_at.date' => 'The execution date must be a valid date.'
        ]);

        $fromAccount = Account::findOrFail($validated['from_account_id']);
        $totalAmount = array_sum($validated['amounts']);

        if ($totalAmount == 0) {
            return back()
                ->withInput()
                ->withErrors(['amounts' => 'Total distribution amount cannot be zero.']);
        }

        DB::transaction(function () use ($validated, $fromAccount) {
            // Process each transfer
            foreach ($validated['amounts'] as $key => $amount) {
                if ($amount != 0) {
                    $toAccount = Account::findOrFail($validated['to_account_ids'][$key]);
                    
                    Transfer::create([
                        'from_account_id' => $amount > 0 ? $validated['from_account_id'] : $validated['to_account_ids'][$key],
                        'to_account_id' => $amount > 0 ? $validated['to_account_ids'][$key] : $validated['from_account_id'],
                        'amount' => abs($amount),
                        'description' => $validated['description'] ?? null,
                        'executed_at' => $validated['executed_at'],
                        'status' => 'completed',
                        'user_id' => auth()->id()
                    ]);

                    // For positive amounts, money flows from source to destination
                    // For negative amounts, money flows from destination to source
                    if ($amount > 0) {
                        $fromAccount->decrement('balance', $amount);
                        $toAccount->increment('balance', $amount);
                    } else {
                        $fromAccount->increment('balance', abs($amount));
                        $toAccount->decrement('balance', abs($amount));
                    }
                }
            }
        });

        return redirect()
            ->route('transfers.distribute')
            ->with('success', 'Distribution completed successfully. Total amount: ' . number_format($totalAmount, 0) . ' FCFA');
    }

    public function destroy(Transfer $transfer)
    {
        DB::transaction(function () use ($transfer) {
            // If this is a completed transfer, reverse the transaction
            if ($transfer->status === 'completed') {
                $fromAccount = Account::findOrFail($transfer->from_account_id);
                $toAccount = Account::findOrFail($transfer->to_account_id);
                
                // Reverse the transfer amounts
                $fromAccount->increment('balance', $transfer->amount);
                $toAccount->decrement('balance', $transfer->amount);
            }

            $transfer->delete();
        });

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
