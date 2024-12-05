@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create New Transfer</h5>
                    <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Transfers
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('transfers.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="from_account_id" class="form-label">From Account</label>
                            <select class="form-select @error('from_account_id') is-invalid @enderror" 
                                    id="from_account_id" 
                                    name="from_account_id" 
                                    required>
                                <option value="">Select Source Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('from_account_id') == $account->id ? 'selected' : '' }}
                                            data-balance="{{ $account->balance }}">
                                        {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                                    </option>
                                @endforeach
                            </select>
                            @error('from_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="to_account_id" class="form-label">To Account</label>
                            <select class="form-select @error('to_account_id') is-invalid @enderror" 
                                    id="to_account_id" 
                                    name="to_account_id" 
                                    required>
                                <option value="">Select Destination Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ number_format($account->balance, 2) }} XAF)
                                    </option>
                                @endforeach
                            </select>
                            @error('to_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (XAF)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       value="{{ old('amount', '0.00') }}" 
                                       step="0.01" 
                                       min="0.01" 
                                       required>
                                <span class="input-group-text">XAF</span>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="executed_at" class="form-label">Execution Date</label>
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

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right-circle"></i> Create Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromAccountSelect = document.getElementById('from_account_id');
    const toAccountSelect = document.getElementById('to_account_id');

    function updateToAccountOptions() {
        const fromAccountId = fromAccountSelect.value;
        
        Array.from(toAccountSelect.options).forEach(option => {
            if (option.value === fromAccountId) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });

        // If selected 'to' account is now disabled, reset selection
        if (toAccountSelect.value === fromAccountId) {
            toAccountSelect.value = '';
        }
    }

    fromAccountSelect.addEventListener('change', updateToAccountOptions);
});
</script>
@endpush
