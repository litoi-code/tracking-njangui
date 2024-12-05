@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Today's Receiving Volume</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayVolumes as $volume)
                                    <tr>
                                        <td>
                                            <a href="{{ route('accounts.show', $volume['account']) }}" class="text-decoration-none">
                                                {{ $volume['account']->name }}
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($volume['volume'], 2) }} XAF
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No transfers received today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Transfers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransfers as $transfer)
                                    <tr>
                                        <td>{{ $transfer->executed_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('accounts.show', $transfer->fromAccount) }}" class="text-decoration-none">
                                                {{ $transfer->fromAccount->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('accounts.show', $transfer->toAccount) }}" class="text-decoration-none">
                                                {{ $transfer->toAccount->name }}
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($transfer->amount, 2) }} XAF
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No recent transfers</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
