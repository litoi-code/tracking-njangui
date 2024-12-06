@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Accounts</h2>
                <div class="d-flex align-items-center gap-3">
                    <select class="form-select" id="accountTypeFilter" onchange="filterAccounts(this.value)">
                        <option value="all">Tous les types ({{ $accounts->total() }})</option>
                        @foreach($accountTypes as $type)
                            <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->accounts_count }})
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Account
                    </a>
                </div>
            </div>

            @if($accounts->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-wallet2 fs-1 text-muted mb-3 d-block"></i>
                    <h4 class="text-muted">No Accounts Found</h4>
                    <p class="text-muted mb-4">Start by creating your first account to track your finances.</p>
                    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Account
                    </a>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($accounts as $account)
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <a href="{{ route('accounts.show', $account) }}" 
                                                   class="text-decoration-none text-dark">
                                                    {{ $account->name }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-secondary">{{ $account->accountType->name }}</span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-dark p-0" 
                                                    type="button"
                                                    data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('accounts.show', $account) }}">
                                                        <i class="bi bi-eye me-2"></i> View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('accounts.edit', $account) }}">
                                                        <i class="bi bi-pencil me-2"></i> Edit Account
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('accounts.destroy', $account) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="dropdown-item text-danger"
                                                                onclick="return confirm('Are you sure you want to delete this account?')">
                                                            <i class="bi bi-trash me-2"></i> Delete Account
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <div class="text-muted small">Balance</div>
                                        <h4 class="mb-0">{{ number_format($account->balance, 0) }} XAF</h4>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center text-muted small">
                                        <span>Created {{ $account->created_at->format('M d, Y') }}</span>
                                        <a href="{{ route('accounts.show', $account) }}" 
                                           class="text-decoration-none">
                                            View Details <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    @if($accounts->hasPages())
                        {{ $accounts->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#accountTypeFilter').select2({
        theme: 'bootstrap-5',
        width: '200px'
    });
});

function filterAccounts(type) {
    window.location.href = '{{ route('accounts.index') }}' + (type !== 'all' ? '?type=' + type : '');
}
</script>
@endpush
