@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Accounts</h2>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Account
        </a>
    </div>

    <div class="row">
        @foreach($accounts as $account)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-1">{{ $account->name }}</h5>
                            <span class="badge bg-secondary">{{ $account->accountType->name }}</span>
                        </div>
                        <h4 class="text-{{ $account->balance >= 0 ? 'success' : 'danger' }}">
                            {{ number_format($account->balance, 2) }} {{ $account->currency }}
                        </h4>
                    </div>

                    <div class="small text-muted mb-3">
                        Owned by: {{ $account->user->name }}
                    </div>

                    @php
                        $monthlyVolume = $account->getMonthlyTransferVolume();
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">Monthly In</div>
                                <div class="fw-bold text-success">
                                    {{ number_format($monthlyVolume['incoming'], 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">Monthly Out</div>
                                <div class="fw-bold text-danger">
                                    {{ number_format($monthlyVolume['outgoing'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('accounts.show', $account) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Details
                        </a>
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to delete this account?')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
