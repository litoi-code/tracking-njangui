@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Create New Loan</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('loans.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="from_account_id" class="form-label">Source Account</label>
                        <select name="from_account_id" id="from_account_id" class="form-select select2 @error('from_account_id') is-invalid @enderror" required>
                            <option value="">Select an account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                    {{ $account->name }} (Balance: {{ number_format($account->balance, 2) }} FCFA)
                                </option>
                            @endforeach
                        </select>
                        @error('from_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="to_account_id" class="form-label">Beneficiary Account</label>
                        <select name="to_account_id" id="to_account_id" class="form-select select2 @error('to_account_id') is-invalid @enderror" required>
                            <option value="">Select an account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->name }} (Balance: {{ number_format($account->balance, 2) }} FCFA)
                                </option>
                            @endforeach
                        </select>
                        @error('to_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Principal Amount</label>
                        <div class="input-group">
                            <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}" required min="1" step="1">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="interest_rate" class="form-label">Monthly Interest Rate (%)</label>
                        <div class="input-group">
                            <input type="number" name="interest_rate" id="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror"
                                value="{{ old('interest_rate', 2) }}" required min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('interest_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="term_months" class="form-label">Term (in months)</label>
                        <input type="number" name="term_months" id="term_months" class="form-control @error('term_months') is-invalid @enderror"
                            value="{{ old('term_months', 12) }}" required min="1" max="360">
                        @error('term_months')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="monthly_payment" class="form-label">Loan Summary</label>
                        <div class="card">
                            <div class="card-body p-2">
                                <p class="mb-1"><strong>Total Interest:</strong> <span id="total_interest">0</span> FCFA</p>
                                <p class="mb-1"><strong>Total Amount:</strong> <span id="total_amount">0</span> FCFA</p>
                                <p class="mb-1"><strong>Monthly Payment:</strong> <span id="monthly_payment">0</span> FCFA</p>
                                <p class="mb-1"><strong>Monthly Principal:</strong> <span id="monthly_principal">0</span> FCFA</p>
                                <p class="mb-0"><strong>Monthly Interest:</strong> <span id="monthly_interest">0</span> FCFA</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                            rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('loans.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    // Get form elements
    const amountInput = document.getElementById('amount');
    const interestRateInput = document.getElementById('interest_rate');
    const termMonthsInput = document.getElementById('term_months');
    const fromAccountSelect = document.getElementById('from_account_id');
    const toAccountSelect = document.getElementById('to_account_id');

    // Get display elements
    const totalInterestDisplay = document.getElementById('total_interest');
    const totalAmountDisplay = document.getElementById('total_amount');
    const monthlyPaymentDisplay = document.getElementById('monthly_payment');
    const monthlyPrincipalDisplay = document.getElementById('monthly_principal');
    const monthlyInterestDisplay = document.getElementById('monthly_interest');

    // Add event listeners
    [amountInput, interestRateInput, termMonthsInput].forEach(input => {
        input.addEventListener('input', calculateLoanDetails);
    });

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

    function formatNumber(number) {
        return Math.round(number).toLocaleString('en-US');
    }

    function calculateLoanDetails() {
        const principal = parseFloat(amountInput.value) || 0;
        const monthlyRate = parseFloat(interestRateInput.value) || 0;
        const months = parseInt(termMonthsInput.value) || 0;

        if (principal > 0 && months > 0) {
            // Calculate total interest
            const totalInterest = principal * (monthlyRate / 100) * months;
            
            // Calculate total amount
            const totalAmount = principal + totalInterest;
            
            // Calculate monthly payments
            const monthlyPayment = totalAmount / months;
            const monthlyPrincipal = principal / months;
            const monthlyInterest = totalInterest / months;

            // Update displays
            totalInterestDisplay.textContent = formatNumber(totalInterest);
            totalAmountDisplay.textContent = formatNumber(totalAmount);
            monthlyPaymentDisplay.textContent = formatNumber(monthlyPayment);
            monthlyPrincipalDisplay.textContent = formatNumber(monthlyPrincipal);
            monthlyInterestDisplay.textContent = formatNumber(monthlyInterest);
        } else {
            // Reset displays
            totalInterestDisplay.textContent = '0';
            totalAmountDisplay.textContent = '0';
            monthlyPaymentDisplay.textContent = '0';
            monthlyPrincipalDisplay.textContent = '0';
            monthlyInterestDisplay.textContent = '0';
        }
    }

    // Initial calculation
    calculateLoanDetails();
});
</script>
@endpush
