@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $account->name }}</h2>
            <div class="text-muted">
                <span class="badge bg-secondary me-2">{{ $account->accountType->name }}</span>
                Owned by {{ $account->user->name }}
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group">
                <a href="{{ route('accounts.edit', $account) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Current Balance</h5>
                    <h3 class="text-{{ $account->balance >= 0 ? 'success' : 'danger' }} mb-0">
                        {{ $account->formatted_balance }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Monthly Volume</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted d-block">Incoming</small>
                            <span class="text-success">+{{ number_format($monthlyVolume['incoming'], 2) }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Outgoing</small>
                            <span class="text-danger">-{{ number_format($monthlyVolume['outgoing'], 2) }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted d-block">Net Change</small>
                        <span class="text-{{ $monthlyVolume['net'] >= 0 ? 'success' : 'danger' }}">
                            {{ $monthlyVolume['net'] >= 0 ? '+' : '' }}{{ number_format($monthlyVolume['net'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Balance Trend</h5>
                    <canvas id="balanceTrendChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Incoming Transfers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transferHistory['incoming'] as $transfer)
                                <tr>
                                    <td>{{ $transfer->executed_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $transfer->fromAccount->name }}</td>
                                    <td class="text-success">
                                        +{{ number_format($transfer->amount, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No incoming transfers</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Outgoing Transfers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>To</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transferHistory['outgoing'] as $transfer)
                                <tr>
                                    <td>{{ $transfer->executed_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $transfer->toAccount->name }}</td>
                                    <td class="text-danger">
                                        -{{ number_format($transfer->amount, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No outgoing transfers</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Balance Trend Chart
    new Chart(document.getElementById('balanceTrendChart'), {
        type: 'line',
        data: {
            labels: @json($balanceTrend->pluck('month')),
            datasets: [{
                label: 'Net Change',
                data: @json($balanceTrend->pluck('net_change')),
                borderColor: '#4e73df',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
@endsection
