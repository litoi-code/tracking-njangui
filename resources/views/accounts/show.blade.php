<!-- Search and Filter Controls -->
{{-- <div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       id="search" 
                       class="form-control" 
                       placeholder="Search transfers..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="date" 
                       id="start_date" 
                       class="form-control" 
                       placeholder="Start Date"
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" 
                       id="end_date" 
                       class="form-control" 
                       placeholder="End Date"
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <button id="clearFilters" 
                        class="btn btn-outline-secondary w-100">
                    Clear
                </button>
            </div>
        </div>
    </div>
</div> --}}
<!-- Search and Filter Controls -->
{{-- <div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       id="search" 
                       class="form-control" 
                       placeholder="Search transfers..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="date" 
                       id="start_date" 
                       class="form-control" 
                       placeholder="Start Date"
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" 
                       id="end_date" 
                       class="form-control" 
                       placeholder="End Date"
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <button id="clearFilters" 
                        class="btn btn-outline-secondary w-100">
                    Clear
                </button>
            </div>
        </div>
    </div>
</div> --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails du Compte</h5>
                    <div class="btn-group">
                        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h3>{{ $account->name }}</h3>
                            <p class="text-muted mb-2">
                                <i class="bi bi-tag"></i> {{ $account->accountType->name }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <h2>{{ $account->name }}</h2>
                                <h3 class="text-{{ $account->balance >= 0 ? 'success' : 'danger' }}">
                                    {{ number_format($account->balance, 0) }} XAF
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Monthly Statistics</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <p class="text-muted mb-1">Credits</p>
                                            <h5 class="text-success mb-1">{{ number_format($monthlyVolume['incoming'], 0) }} FCFA</h5>
                                            <small class="text-muted">{{ $monthlyVolume['incoming_count'] }} transfers</small>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1">Debits</p>
                                            <h5 class="text-danger mb-1">{{ number_format($monthlyVolume['outgoing'], 0) }} FCFA</h5>
                                            <small class="text-muted">{{ $monthlyVolume['outgoing_count'] }} transfers</small>
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0">Total Transfers</p>
                                                <h5 class="mb-0">{{ $monthlyVolume['incoming_count'] + $monthlyVolume['outgoing_count'] }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Transaction History</h5>
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <input type="text" id="search" class="form-control form-control-sm" 
                                               value="{{ request('search') }}" placeholder="Search account name...">
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" id="start_date" class="form-control form-control-sm" 
                                               value="{{ request('start_date') }}" placeholder="Start Date">
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" id="end_date" class="form-control form-control-sm" 
                                               value="{{ request('end_date') }}" placeholder="End Date">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">Clear</button>
                                    </div>
                                </div>
                            </div>

                            <div id="transfersTable" class="mt-3">
                                @include('accounts._transfers_table', ['transfers' => $transfers, 'account' => $account])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let typingTimer;
    const doneTypingInterval = 500;
    const searchInput = document.getElementById('search');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const clearButton = document.getElementById('clearFilters');
    const transfersTable = document.getElementById('transfersTable');

    // Add CSRF token to all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    function loadTransfers() {
        // Show loading state
        transfersTable.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
        
        const params = new URLSearchParams({
            search: searchInput.value || '',
            start_date: startDateInput.value || '',
            end_date: endDateInput.value || ''
        });

        fetch(`{{ route('accounts.transfers', $account) }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/html'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            transfersTable.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            transfersTable.innerHTML = '<div class="alert alert-danger">Error loading transfers. Please try again.</div>';
        });
    }

    // Debounce search input
    searchInput?.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(loadTransfers, doneTypingInterval);
    });

    // Date input handlers
    startDateInput?.addEventListener('change', loadTransfers);
    endDateInput?.addEventListener('change', loadTransfers);

    // Clear filters
    clearButton?.addEventListener('click', function() {
        searchInput.value = '';
        startDateInput.value = '';
        endDateInput.value = '';
        loadTransfers();
    });

    // Initial load
    loadTransfers();
});
</script>
@endpush
