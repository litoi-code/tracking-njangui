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
                            <select class="form-select select2-accounts @error('from_account_id') is-invalid @enderror" 
                                    id="from_account_id" 
                                    name="from_account_id" 
                                    required>
                                <option value="">Select Source Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('from_account_id') == $account->id ? 'selected' : '' }}
                                            data-balance="{{ $account->balance }}">
                                        {{ $account->name }} ({{ number_format($account->balance, 2) }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                            @error('from_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="to_account_id" class="form-label">To Account</label>
                            <select class="form-select select2-accounts @error('to_account_id') is-invalid @enderror" 
                                    id="to_account_id" 
                                    name="to_account_id" 
                                    required>
                                <option value="">Select Destination Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ number_format($account->balance, 2) }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                            @error('to_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}"
                                       required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="transfer_date" class="form-label">Transfer Date</label>
                            <input type="date" name="transfer_date" id="transfer_date" 
                                   class="form-control @error('transfer_date') is-invalid @enderror"
                                   value="{{ old('transfer_date', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}"
                                   required>
                            <div class="form-text">You can select past dates for backdated transfers</div>
                            @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
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
    // Initialize Select2 for accounts with search
    $('.select2-accounts').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search for an account...',
        allowClear: true,
        minimumInputLength: 1,
        matcher: function(params, data) {
            // If there are no search terms, return all of the data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Do not display the item if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // Search in both account name and balance
            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                return data;
            }

            // Return `null` if the term should not be displayed
            return null;
        }
    });

    // Get form elements
    const fromAccountSelect = document.getElementById('from_account_id');
    const toAccountSelect = document.getElementById('to_account_id');

    // Prevent selecting same account
    toAccountSelect.addEventListener('change', function() {
        if (this.value === fromAccountSelect.value && this.value !== '') {
            alert('Beneficiary account must be different from source account');
            this.value = '';
            $(this).trigger('change.select2');
        }
    });

    fromAccountSelect.addEventListener('change', function() {
        if (this.value === toAccountSelect.value && this.value !== '') {
            alert('Source account must be different from beneficiary account');
            toAccountSelect.value = '';
            $(toAccountSelect).trigger('change.select2');
        }
    });
});
</script>
@endpush
@endsection
