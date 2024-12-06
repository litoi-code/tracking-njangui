@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Distribution</h5>
                        <div class="d-flex align-items-center gap-3">
                            <span id="total_amount" class="badge bg-primary fs-6">Total: 0 XAF</span>
                            <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('transfers.distribute.store') }}" method="POST" id="distributeForm">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="from_account_id" class="form-label">Source Account</label>
                                    <select name="from_account_id" id="from_account_id" class="form-select @error('from_account_id') is-invalid @enderror" required>
                                        <option value="">Select source account</option>
                                        @foreach($groupedAccounts as $type => $accounts)
                                            <optgroup label="{{ $type }}">
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}" 
                                                        {{ old('from_account_id') == $account->id ? 'selected' : '' }}
                                                        data-balance="{{ $account->balance }}">
                                                        {{ $account->name }} (Balance: {{ number_format($account->balance, 0) }} XAF)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('from_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="executed_at" class="form-label">Execution Date</label>
                                    <input type="date" class="form-control @error('executed_at') is-invalid @enderror" 
                                           id="executed_at" name="executed_at" 
                                           value="{{ old('executed_at', date('Y-m-d')) }}" required>
                                    @error('executed_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Distribution Progress</label>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        <span class="progress-text">0%</span>
                                    </div>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <small class="text-muted">Total Amount: <span class="total-amount fw-bold">0</span> XAF</small>
                                    <small class="text-muted">Available: <span class="available-amount fw-bold">0</span> XAF</small>
                                </div>
                            </div>
                        </div>

                        <div class="distribution-rows">
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <thead>
                                                <tr>
                                                    <th style="width: 45%">Destination Account</th>
                                                    <th style="width: 30%">Amount</th>
                                                    <th style="width: 25%">Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($savingsAccounts as $index => $account)
                                                <tr class="distribution-row">
                                                    <td>
                                                        <input type="hidden" name="to_account_ids[]" value="{{ $account->id }}">
                                                        <span class="form-control-plaintext">{{ $account->name }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input type="number" 
                                                                class="form-control distribution-amount" 
                                                                name="amounts[]" 
                                                                step="any"
                                                                value="0"
                                                                placeholder="Enter amount (positive or negative)">
                                                            <span class="input-group-text">XAF</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="form-control-plaintext">
                                                            {{ number_format($account->balance, 2) }} XAF
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Submit Distribution
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress {
        background-color: #e9ecef;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
    }
    .progress-bar {
        transition: width 0.3s ease;
        position: relative;
        border-radius: 0.5rem;
    }
    .progress-bar.under {
        background-color: #0d6efd;
    }
    .progress-bar.exact {
        background-color: #198754;
    }
    .progress-bar.over {
        background-color: #dc3545;
    }
    .progress-text {
        position: absolute;
        width: 100%;
        text-align: center;
        font-weight: bold;
        color: white;
        text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
    }
    .total-amount, .available-amount {
        font-size: 1.1em;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromAccountSelect = document.querySelector('#from_account_id');
    let sourceBalance = 0;

    // Initialize Select2 for source account
    $('#from_account_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select source account',
        allowClear: true
    });

    // Initialize Flatpickr date picker
    flatpickr("#executed_at", {
        dateFormat: "Y-m-d",
        allowInput: true,
        defaultDate: new Date()
    });

    // Format number to currency without decimals
    function formatCurrency(amount) {
        return new Intl.NumberFormat('fr-FR', {
            maximumFractionDigits: 0,
            minimumFractionDigits: 0
        }).format(amount);
    }

    // Calculate total and update UI
    function updateTotal() {
        const amounts = [...document.querySelectorAll('.distribution-amount')].map(input => parseFloat(input.value) || 0);
        const total = amounts.reduce((sum, amount) => sum + amount, 0);
        const available = sourceBalance - total;
        
        // Update total amount display
        document.querySelector('.total-amount').textContent = formatCurrency(total);
        document.querySelector('.available-amount').textContent = formatCurrency(Math.max(0, available));
        document.querySelector('#total_amount').textContent = 'Total: ' + formatCurrency(total) + ' XAF';

        // Update progress bar
        if (sourceBalance > 0) {
            const percentage = (total / sourceBalance) * 100;
            const progressBar = document.querySelector('.progress-bar');
            const progressText = progressBar.querySelector('.progress-text');
            
            // Update width and text
            progressBar.style.width = Math.min(percentage, 100) + '%';
            progressBar.setAttribute('aria-valuenow', Math.min(percentage, 100));
            progressText.textContent = percentage.toFixed(1) + '%';
            
            // Update colors based on percentage
            progressBar.classList.remove('under', 'exact', 'over');
            if (percentage < 100) {
                progressBar.classList.add('under');
            } else if (percentage === 100) {
                progressBar.classList.add('exact');
            } else {
                progressBar.classList.add('over');
            }

            // Update available amount color
            const availableElement = document.querySelector('.available-amount');
            availableElement.classList.remove('text-danger', 'text-success');
            if (available < 0) {
                availableElement.classList.add('text-danger');
            } else {
                availableElement.classList.add('text-success');
            }
        }
    }

    // Update source balance when changing source account
    fromAccountSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        sourceBalance = selectedOption.value ? parseInt(selectedOption.dataset.balance) : 0;
        updateTotal();
    });

    // Update total when changing any amount
    document.querySelector('tbody').addEventListener('input', function(e) {
        if (e.target.classList.contains('distribution-amount')) {
            updateTotal();
        }
    });
});
</script>
@endpush
@endsection
