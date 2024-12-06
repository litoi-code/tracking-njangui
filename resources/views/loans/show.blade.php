@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Détails du Prêt #{{ $loan->id }}</h5>
                        <div class="d-flex gap-2">
                            @if($loan->status === 'active')
                            <form action="{{ route('loans.make-payment', $loan) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Confirmer le paiement mensuel?')">
                                    <i class="bi bi-cash"></i> Effectuer un paiement
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('loans.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Informations du prêt</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Compte</th>
                                    <td>{{ $loan->account->name }}</td>
                                </tr>
                                <tr>
                                    <th>Montant</th>
                                    <td>{{ number_format($loan->amount, 0) }} XAF</td>
                                </tr>
                                <tr>
                                    <th>Taux d'intérêt</th>
                                    <td>{{ $loan->interest_rate }}%</td>
                                </tr>
                                <tr>
                                    <th>Durée</th>
                                    <td>{{ $loan->term_months }} mois</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Statut actuel</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'completed' ? 'info' : 'warning') }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Paiement mensuel</th>
                                    <td>{{ number_format($loan->monthly_payment, 0) }} XAF</td>
                                </tr>
                                <tr>
                                    <th>Solde restant</th>
                                    <td>{{ number_format($loan->remaining_balance, 0) }} XAF</td>
                                </tr>
                                <tr>
                                    <th>Prochain paiement</th>
                                    <td>{{ $loan->next_payment_date ? $loan->next_payment_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="mb-3">Tableau d'amortissement</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="amortizationTable">
                                    <thead>
                                        <tr>
                                            <th>Mois</th>
                                            <th>Paiement</th>
                                            <th>Principal</th>
                                            <th>Intérêts</th>
                                            <th>Solde restant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center">Chargement...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch(`/loans/${@json($loan->id)}/amortization`)
        .then(response => response.json())
        .then(schedule => {
            const tbody = document.querySelector('#amortizationTable tbody');
            tbody.innerHTML = '';
            
            schedule.forEach(row => {
                tbody.innerHTML += `
                    <tr>
                        <td>${row.month}</td>
                        <td>${new Intl.NumberFormat('fr-FR').format(row.payment.toFixed(0))} XAF</td>
                        <td>${new Intl.NumberFormat('fr-FR').format(row.principal.toFixed(0))} XAF</td>
                        <td>${new Intl.NumberFormat('fr-FR').format(row.interest.toFixed(0))} XAF</td>
                        <td>${new Intl.NumberFormat('fr-FR').format(row.balance.toFixed(0))} XAF</td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('#amortizationTable tbody').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Erreur lors du chargement du tableau d'amortissement
                    </td>
                </tr>
            `;
        });
});
</script>
@endpush
@endsection
