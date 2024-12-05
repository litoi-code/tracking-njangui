@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create New Transaction</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="from_account_id">From Account</label>
                            <select class="form-control @error('from_account_id') is-invalid @enderror" 
                                    id="from_account_id" name="from_account_id" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} (Balance: ${{ number_format($account->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('from_account_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="to_account_id">To Account</label>
                            <select class="form-control @error('to_account_id') is-invalid @enderror" 
                                    id="to_account_id" name="to_account_id" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} (Balance: ${{ number_format($account->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('to_account_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" name="amount" value="{{ old('amount', '0.00') }}" required>
                            @error('amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="interest">Interest (Optional)</label>
                            <input type="number" step="0.01" class="form-control @error('interest') is-invalid @enderror" 
                                   id="interest" name="interest" value="{{ old('interest', '0.00') }}">
                            @error('interest')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Transaction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
