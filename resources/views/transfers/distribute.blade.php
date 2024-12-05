@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Distribute Funds</h2>
        <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Transfers
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('transfers.distribute.store') }}" method="POST" id="distributeForm" onsubmit="return validateForm()">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="from_account_id" class="form-label">From Account</label>
                            <input type="text" id="sourceSearch" class="form-control mb-2" placeholder="Search source account...">
                            <select name="from_account_id" id="from_account_id" class="form-select @error('from_account_id') is-invalid @enderror" required>
                                <option value="">Select Source Account</option>
                                @foreach($sourceAccounts as $account)
                                    <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                        {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                                    </option>
                                @endforeach
                            </select>
                            @error('from_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="from_account_balance" class="form-text"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="executed_at" class="form-label">Execution Date</label>
                            <input type="datetime-local" name="executed_at" id="executed_at" 
                                   class="form-control @error('executed_at') is-invalid @enderror" 
                                   value="{{ old('executed_at', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('executed_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Distributions</label>
                    <div class="mb-2">
                        <input type="text" id="destinationSearch" class="form-control" placeholder="Search destination accounts...">
                    </div>
                    <div id="distributionsContainer">
                        <div class="distribution-row mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="distributions[0][to_account_id]" class="form-select to-account" required>
                                        <option value="">Select Destination Account</option>
                                        @foreach($destinationAccounts as $account)
                                            <option value="{{ $account->id }}">
                                                {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="distributions[0][amount]" 
                                           class="form-control amount-input" step="0.01" min="0.01" 
                                           placeholder="Amount" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-distribution" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addDistribution" class="btn btn-secondary">
                        <i class="bi bi-plus-circle"></i> Add Distribution
                    </button>
                </div>

                <div class="text-end">
                    <div id="total_amount" class="mb-2 h5"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Create Distributions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const distributionsContainer = document.getElementById('distributionsContainer');
    const addDistributionButton = document.getElementById('addDistribution');
    const sourceSearch = document.getElementById('sourceSearch');
    const destinationSearch = document.getElementById('destinationSearch');
    let distributionCount = 0;

    // Source account search
    sourceSearch.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const sourceSelect = document.getElementById('from_account_id');
        const options = sourceSelect.querySelectorAll('option:not(:first-child)');
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Destination account search
    destinationSearch.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const accountSelects = document.querySelectorAll('.to-account');
        
        accountSelects.forEach(select => {
            const options = select.querySelectorAll('option:not(:first-child)');
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    });

    function createDistributionRow() {
        distributionCount++;
        const row = document.createElement('div');
        row.className = 'distribution-row mb-3';
        row.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <select name="distributions[${distributionCount}][to_account_id]" class="form-select to-account" required>
                        <option value="">Select Destination Account</option>
                        @foreach($destinationAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" name="distributions[${distributionCount}][amount]" 
                           class="form-control amount-input" step="0.01" min="0.01" 
                           placeholder="Amount" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-distribution">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        distributionsContainer.appendChild(row);

        // Add event listener to the new remove button
        row.querySelector('.remove-distribution').addEventListener('click', function() {
            row.remove();
            updateTotalAmount();
            updateRemoveButtons();
        });

        // Add event listener to the new amount input
        row.querySelector('.amount-input').addEventListener('input', updateTotalAmount);

        updateRemoveButtons();
        return row;
    }

    function updateRemoveButtons() {
        const removeButtons = document.querySelectorAll('.remove-distribution');
        const hasMultipleRows = removeButtons.length > 1;
        
        removeButtons.forEach(button => {
            button.disabled = !hasMultipleRows;
        });
    }

    function updateTotalAmount() {
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total_amount').textContent = `Total: ${total.toFixed(2)} XAF`;
    }

    // Event Listeners
    addDistributionButton.addEventListener('click', function() {
        const newRow = createDistributionRow();
        newRow.querySelector('.amount-input').addEventListener('input', updateTotalAmount);
    });

    // Initialize the first row's amount input listener
    document.querySelector('.amount-input').addEventListener('input', updateTotalAmount);

    // Update the remove buttons initially
    updateRemoveButtons();
});
</script>
@endpush
