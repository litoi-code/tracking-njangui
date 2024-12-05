@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Transfers</h2>
        <div class="btn-group">
            <a href="{{ route('transfers.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Single Transfer
            </a>
            <a href="{{ route('transfers.bulk') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-right"></i> Bulk Transfer
            </a>
            <a href="{{ route('transfers.distribute') }}" class="btn btn-info text-white">
                <i class="bi bi-diagram-3"></i> Distribute Funds
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
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
                                {{ number_format($transfer->amount, 2) }} {{ $transfer->fromAccount->currency }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $transfer->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($transfer->description, 30) }}</td>
                            <td>
                                <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No transfers found
                                </div>
                                <a href="{{ route('transfers.create') }}" class="btn btn-primary mt-2">
                                    Create First Transfer
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $transfers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
