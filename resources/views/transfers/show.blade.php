@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Transfer Details</h2>
        <div>
            <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Transfers
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">Transfer Information</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">{{ number_format($transfer->amount, 2) }} {{ $transfer->fromAccount->currency }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $transfer->status === 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($transfer->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Execution Date</dt>
                        <dd class="col-sm-8">{{ $transfer->executed_at->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $transfer->description ?? 'No description provided' }}</dd>
                    </dl>
                </div>

                <div class="col-md-6">
                    <h5 class="card-title">Account Details</h5>
                    <dl class="row">
                        <dt class="col-sm-4">From Account</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('accounts.show', $transfer->fromAccount) }}" class="text-decoration-none">
                                {{ $transfer->fromAccount->name }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">To Account</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('accounts.show', $transfer->toAccount) }}" class="text-decoration-none">
                                {{ $transfer->toAccount->name }}
                            </a>
                        </dd>
                    </dl>
                </div>
            </div>

            @if($transfer->status !== 'completed')
                <div class="mt-4">
                    <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this transfer?')">
                            <i class="bi bi-x-circle"></i> Cancel Transfer
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
