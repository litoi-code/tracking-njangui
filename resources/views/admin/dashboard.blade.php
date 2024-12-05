@extends('layouts.app')

@section('content')
    <h2>Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Total Accounts</h5>
                </div>
                <div class="card-body">
                    <h1>{{ $totalAccounts }}</h1>
                    <a href="{{ route('accounts.index') }}" class="btn btn-primary">Manage Accounts</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Total Transactions</h5>
                </div>
                <div class="card-body">
                    <h1>{{ $totalTransactions }}</h1>
                    <a href="{{ route('transactions.index') }}" class="btn btn-primary">Manage Transactions</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h3>Recent Transactions</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>From Account</th>
                        <th>To Account</th>
                        <th>Amount</th>
                        <th>Interest</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->fromAccount->name }}</td>
                            <td>{{ $transaction->toAccount->name }}</td>
                            <td>{{ $transaction->amount }}</td>
                            <td>{{ $transaction->interest }}</td>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
