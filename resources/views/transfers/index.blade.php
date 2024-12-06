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
            <form action="{{ route('transfers.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="from_account" class="form-label">From Account</label>
                        <select name="from_account" id="from_account" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('from_account') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="to_account" class="form-label">To Account</label>
                        <select name="to_account" id="to_account" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('to_account') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="min_amount" class="form-label">Min Amount</label>
                        <input type="number" name="min_amount" id="min_amount" class="form-control" value="{{ request('min_amount') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="max_amount" class="form-label">Max Amount</label>
                        <input type="number" name="max_amount" id="max_amount" class="form-control" value="{{ request('max_amount') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="text" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="text" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="d-grid gap-2 d-md-flex w-100">
                            <button type="submit" class="btn btn-primary me-md-2">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From Account</th>
                            <th>To Account</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
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
                                <td class="text-end">{{ number_format($transfer->amount, 0) }} XAF</td>
                                <td>
                                    <span class="badge bg-{{ $transfer->status === 'completed' ? 'success' : ($transfer->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($transfer->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('transfers.edit', $transfer) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this transfer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    No transfers found.
                                    <a href="{{ route('transfers.create') }}" class="btn btn-sm btn-success ms-2">
                                        <i class="bi bi-plus-circle"></i>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for account dropdowns
    $('#from_account, #to_account').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select account',
        allowClear: true
    });

    // Initialize Flatpickr for date inputs
    flatpickr("#date_from, #date_to", {
        dateFormat: "Y-m-d",
        allowInput: true
    });
});
</script>
@endpush
