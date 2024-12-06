@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Transfer</h5>
                    <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Transfers
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('transfers.update', $transfer) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="from_account_id" class="form-label">From Account</label>
                            <select name="from_account_id" id="from_account_id" 
                                    class="form-select @error('from_account_id') is-invalid @enderror" required>
                                <option value="">Select source account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('from_account_id', $transfer->from_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} (Balance: {{ number_format($account->balance, 0) }} XAF)
                                    </option>
                                @endforeach
                            </select>
                            @error('from_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="to_account_id" class="form-label">To Account</label>
                            <select name="to_account_id" id="to_account_id" 
                                    class="form-select @error('to_account_id') is-invalid @enderror" required>
                                <option value="">Select destination account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('to_account_id', $transfer->to_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (XAF)</label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" name="amount" value="{{ old('amount', $transfer->amount) }}" 
                                   min="1" step="1" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="executed_at" class="form-label">Execution Date</label>
                            <input type="date" class="form-control @error('executed_at') is-invalid @enderror" 
                                   id="executed_at" name="executed_at" 
                                   value="{{ old('executed_at', $transfer->executed_at->format('Y-m-d')) }}" required>
                            @error('executed_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $transfer->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Transfer
                            </button>
                            <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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

    // Initialize Select2 for account dropdowns
    $('#from_account_id, #to_account_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select account'
    });

    // Initial setup
    updateToAccountOptions();
});
</script>
@endpush
