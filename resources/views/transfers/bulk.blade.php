@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Bulk Transfer</h2>
        <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Transfers
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('transfers.bulk.store') }}" method="POST" id="bulkTransferForm">
                @csrf

                <div class="mb-4">
                    <label for="executed_at" class="form-label">Transfer Date</label>
                    <input type="datetime-local" 
                           class="form-control @error('executed_at') is-invalid @enderror" 
                           id="executed_at" 
                           name="executed_at" 
                           value="{{ old('executed_at', now()->format('Y-m-d\TH:i')) }}" 
                           required>
                    @error('executed_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="transfersContainer">
                    <div class="transfer-row mb-4 border rounded p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">From Account</label>
                                <select class="form-select" name="transfers[0][from_account_id]" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                            {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">To Account</label>
                                <select class="form-select" name="transfers[0][to_account_id]" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Amount (XAF)</label>
                                <input type="number" 
                                       class="form-control" 
                                       name="transfers[0][amount]" 
                                       step="0.01" 
                                       min="0.01" 
                                       required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger remove-transfer" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary" id="addTransfer">
                        <i class="bi bi-plus-circle"></i> Add Another Transfer
                    </button>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Process Bulk Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let transferCount = 1;

document.getElementById('addTransfer').addEventListener('click', function() {
    const container = document.getElementById('transfersContainer');
    const template = container.children[0].cloneNode(true);
    
    // Update names and clear values
    template.querySelectorAll('select, input').forEach(element => {
        if (element.name) {
            element.name = element.name.replace('[0]', `[${transferCount}]`);
        }
        if (element.type !== 'button') {
            element.value = '';
        }
    });

    // Enable remove button
    template.querySelector('.remove-transfer').disabled = false;
    
    container.appendChild(template);
    transferCount++;
});

document.getElementById('transfersContainer').addEventListener('click', function(e) {
    if (e.target.closest('.remove-transfer')) {
        const row = e.target.closest('.transfer-row');
        if (document.querySelectorAll('.transfer-row').length > 1) {
            row.remove();
        }
    }
});

document.getElementById('bulkTransferForm').addEventListener('submit', function(e) {
    let isValid = true;
    const transfers = document.querySelectorAll('.transfer-row');
    
    transfers.forEach(row => {
        const fromAccount = row.querySelector('[name$="[from_account_id]"]');
        const toAccount = row.querySelector('[name$="[to_account_id]"]');
        
        if (fromAccount.value === toAccount.value) {
            alert('From and To accounts cannot be the same');
            isValid = false;
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection
