@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tableau de bord</h2>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Volume mensuel des Caisses</h5>
                    <div class="text-muted">{{ now()->format('l, F j, Y') }}</div>
                </div>
                <div class="card-body">
                    <div id="volumeChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Monthly Transfers</h6>
                            <h3 class="mb-0">{{ $monthlyTransfers }}</h3>
                            <p class="text-muted mb-0">Total Amount: {{ number_format($totalAmount, 2) }}</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Accounts</h6>
                            <h3 class="mb-0">{{ $activeAccounts }}</h3>
                            <p class="text-muted mb-0">Total Accounts: {{ $totalAccounts }}</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Loans</h6>
                            <h3 class="mb-0">{{ $activeLoans }}</h3>
                            <p class="text-muted mb-0">Total Amount: {{ number_format($totalLoansAmount, 2) }}</p>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-hand-holding-usd fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Volume Chart
    const volumeData = @json($mergedVolumes);
    const accounts = @json($receivingAccounts);
    
    // Create series data for each account
    const series = accounts.map(account => ({
        name: account,
        data: volumeData.map(m => m.accounts[account] || 0)
    }));

    const options = {
        series: series,
        chart: {
            type: 'area',
            height: 350,
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            },
            fontFamily: 'inherit'
        },
        plotOptions: {
            area: {
                fillTo: 'end'
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            lineCap: 'round'
        },
        xaxis: {
            categories: volumeData.map(m => m.month),
            labels: {
                rotate: 0,
                style: {
                    fontSize: '14px',
                    fontWeight: 600
                }
            },
            title: {
                text: 'Mois',
                style: {
                    fontSize: '16px',
                    fontWeight: 600
                }
            },
            axisBorder: {
                show: true
            },
            axisTicks: {
                show: true
            }
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return new Intl.NumberFormat('fr-FR').format(value) + ' XAF';
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 500
                }
            },
            title: {
                text: 'Volume',
                style: {
                    fontSize: '16px',
                    fontWeight: 600
                }
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            style: {
                fontSize: '14px'
            },
            y: {
                formatter: function(value) {
                    return new Intl.NumberFormat('fr-FR').format(value) + ' XAF';
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.8,
                opacityTo: 0.3,
                shadeIntensity: 1,
                stops: [0, 100],
                type: 'vertical'
            }
        },
        colors: ['#198754', '#0d6efd', '#6f42c1', '#fd7e14', '#dc3545'],
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4,
            padding: {
                bottom: 15
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            fontSize: '14px',
            fontWeight: 500,
            markers: {
                width: 16,
                height: 16,
                radius: 4
            },
            itemMargin: {
                horizontal: 10,
                vertical: 8
            }
        }
    };

    const chart = new ApexCharts(document.querySelector("#volumeChart"), options);
    chart.render();
});
</script>
@endpush

@push('styles')
<style>
    .card-header .btn-link {
        text-decoration: none;
        color: #6c757d;
        transition: color 0.2s ease-in-out;
    }
    
    .card-header .btn-link:hover {
        color: #0d6efd;
    }

    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }

    .table td {
        vertical-align: middle;
    }

    .transfer-icon {
        font-size: 1.2em;
        color: #6c757d;
    }
</style>
@endpush
