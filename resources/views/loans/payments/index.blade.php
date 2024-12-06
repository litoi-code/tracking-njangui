@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Loan Payment Schedule</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Loan Details</h5>
                    <p class="mb-1"><strong>Principal Amount:</strong> {{ number_format($loan->amount, 2) }} FCFA</p>
                    <p class="mb-1"><strong>Monthly Interest Rate:</strong> {{ $loan->interest_rate }}%</p>
                    <p class="mb-1"><strong>Term:</strong> {{ $loan->term_months }} months</p>
                    <p class="mb-1"><strong>Total Interest:</strong> {{ number_format($loan->amount * ($loan->interest_rate/100) * $loan->term_months, 2) }} FCFA</p>
                    <p class="mb-1"><strong>Total Amount:</strong> {{ number_format($loan->amount * (1 + ($loan->interest_rate/100) * $loan->term_months), 2) }} FCFA</p>
                    <p class="mb-1"><strong>Monthly Payment:</strong> {{ number_format(($loan->amount * (1 + ($loan->interest_rate/100) * $loan->term_months)) / $loan->term_months, 2) }} FCFA</p>
                    <p class="mb-1"><strong>Remaining Balance:</strong> {{ number_format($loan->remaining_balance, 2) }} FCFA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Due Date</th>
                    <th>Monthly Payment</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Remaining Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr class="{{ $payment->status === 'overdue' ? 'table-danger' : ($payment->status === 'paid' ? 'table-success' : '') }}">
                    <td>{{ $payment->due_date->format('d/m/Y') }}</td>
                    <td>{{ number_format($payment->amount, 2) }} FCFA</td>
                    <td>{{ number_format($payment->principal_amount, 2) }} FCFA</td>
                    <td>{{ number_format($payment->interest_amount, 2) }} FCFA</td>
                    <td>{{ number_format($payment->remaining_balance, 2) }} FCFA</td>
                    <td>
                        @if($payment->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($payment->status === 'overdue')
                            <span class="badge bg-danger">Overdue</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($payment->status !== 'paid')
                        <form action="{{ route('loans.payments.make', ['loan' => $loan->id, 'payment' => $payment->id]) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to make this payment?')">
                                Pay
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
