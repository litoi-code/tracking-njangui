@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gestion des Prêts</h5>
                        <a href="{{ route('loans.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Nouveau Prêt
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Compte</th>
                                    <th>Montant</th>
                                    <th>Taux d'intérêt</th>
                                    <th>Durée (mois)</th>
                                    <th>Paiement mensuel</th>
                                    <th>Solde restant</th>
                                    <th>Statut</th>
                                    <th>Prochain paiement</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loans as $loan)
                                <tr>
                                    <td>{{ $loan->id }}</td>
                                    <td>{{ $loan->account->name }}</td>
                                    <td>{{ number_format($loan->amount, 0) }} XAF</td>
                                    <td>{{ $loan->interest_rate }}%</td>
                                    <td>{{ $loan->term_months }}</td>
                                    <td>{{ number_format($loan->monthly_payment, 0) }} XAF</td>
                                    <td>{{ number_format($loan->remaining_balance, 0) }} XAF</td>
                                    <td>
                                        <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'completed' ? 'info' : 'warning') }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $loan->next_payment_date ? $loan->next_payment_date->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($loan->status === 'active')
                                            <form action="{{ route('loans.make-payment', $loan) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirmer le paiement mensuel?')">
                                                    <i class="bi bi-cash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $loans->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
