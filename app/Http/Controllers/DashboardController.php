<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get monthly statistics
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Transfer statistics
        $monthlyTransfers = Transfer::whereBetween('executed_at', [$startOfMonth, $endOfMonth])->count();
        $totalAmount = Transfer::whereBetween('executed_at', [$startOfMonth, $endOfMonth])->sum('amount');
        
        // Account statistics
        $totalAccounts = Account::count();
        $activeAccounts = Account::where('balance', '>', 0)->count();
        
        // Loan statistics
        $activeLoans = Loan::where('status', 'active')->count();
        $totalLoansAmount = Loan::where('status', 'active')->sum('amount');

        // Get yearly receiving volumes by month and account
        $yearlyVolumes = Transfer::selectRaw('MONTH(executed_at) as month_num, YEAR(executed_at) as year, to_account_id, SUM(amount) as total_amount')
            ->whereYear('executed_at', now()->year)
            ->whereNotNull('to_account_id')
            ->with('toAccount')
            ->groupBy('year', 'month_num', 'to_account_id')
            ->orderBy('year')
            ->orderBy('month_num')
            ->get()
            ->groupBy('month_num')
            ->map(function ($monthData) {
                $accountData = [];
                foreach ($monthData as $data) {
                    $accountData[$data->toAccount->name] = (int) $data->total_amount;
                }
                return [
                    'month_num' => $monthData[0]->month_num,
                    'month' => Carbon::create(null, $monthData[0]->month_num)->format('M'),
                    'accounts' => $accountData
                ];
            })->values();

        // Get accounts that received money this year
        $accountVolumes = Transfer::whereYear('executed_at', now()->year)
            ->whereNotNull('to_account_id')
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

        // Get unique account names that received money
        $receivingAccounts = $accountVolumes->pluck('account.name')->unique()->values()->all();

        // Create array for all months
        $allMonths = [];
        for ($i = 1; $i <= 12; $i++) {
            $allMonths[] = [
                'month_num' => $i,
                'month' => Carbon::create(null, $i)->format('M'),
                'accounts' => []
            ];
        }

        // Merge existing data with all months
        $mergedVolumes = collect($allMonths)->map(function ($month) use ($yearlyVolumes) {
            $existingData = $yearlyVolumes->firstWhere('month_num', $month['month_num']);
            return [
                'month' => $month['month'],
                'accounts' => $existingData ? $existingData['accounts'] : []
            ];
        });

        return view('dashboard', compact(
            'monthlyTransfers',
            'totalAmount',
            'totalAccounts',
            'activeAccounts',
            'activeLoans',
            'totalLoansAmount',
            'mergedVolumes',
            'receivingAccounts'
        ));
    }

    private function getTransferChartData()
    {
        $months = collect([]);
        $transferData = collect([]);
        
        // Get last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));
            
            $monthlyTotal = Transfer::whereYear('executed_at', $date->year)
                ->whereMonth('executed_at', $date->month)
                ->sum('amount');
                
            $transferData->push($monthlyTotal);
        }

        return [
            'labels' => $months->toArray(),
            'data' => $transferData->toArray()
        ];
    }
}
