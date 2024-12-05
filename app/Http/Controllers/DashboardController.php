<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get today's volume for accounts that received money
        $todayVolumes = Transfer::whereDate('executed_at', today())
            ->where('status', 'completed')
            ->select('to_account_id', DB::raw('SUM(amount) as volume'))
            ->groupBy('to_account_id')
            ->with('toAccount')
            ->get()
            ->map(function ($transfer) {
                return [
                    'account' => $transfer->toAccount,
                    'volume' => $transfer->volume
                ];
            });

        // Get recent transfers
        $recentTransfers = Transfer::with(['fromAccount', 'toAccount'])
            ->where('status', 'completed')
            ->latest('executed_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('todayVolumes', 'recentTransfers'));
    }
}
