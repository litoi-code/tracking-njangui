@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Transactions</h5>
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">Create New Transaction</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>From Account</th>
                                    <th>To Account</th>
                                    <th>Amount</th>
                                    <th>Interest</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->fromAccount->name }}</td>
                                        <td>{{ $transaction->toAccount->name }}</td>
                                        <td>${{ number_format($transaction->amount, 2) }}</td>
                                        <td>${{ number_format($transaction->interest, 2) }}</td>
                                        <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('transactions.edit', $transaction) }}" 
                                                   class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('transactions.destroy', $transaction) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this transaction?');"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
