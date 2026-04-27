@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Chart s Section - Two Charts Side by Side -->
        <div class="row mb-4">
            <!-- Paid Orders Chart -->
            <div class="col-xl-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line text-primary ms-2"></i>
                                الطلبات المدفوعة - آخر أسبوعين
                            </h5>
                            <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ \Carbon\Carbon::now()->subDays(13)->format('Y-m-d') }} - {{ \Carbon\Carbon::now()->format('Y-m-d') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-around mb-4">
                            <div class="text-center">
                                <h3 class="text-primary fw-bold mb-0">{{ array_sum($paidOrdersChart['data']) }}</h3>
                                <small class="text-muted">إجمالي الطلبات المدفوعة</small>
                            </div>
                            <div class="text-center">
                                <h3 class="text-success fw-bold mb-0">{{ max($paidOrdersChart['data']) }}</h3>
                                <small class="text-muted">أعلى يوم</small>
                            </div>
                            <div class="text-center">
                                <h3 class="text-info fw-bold mb-0">{{ number_format(array_sum($paidOrdersChart['data']) / 14, 1) }}</h3>
                                <small class="text-muted">المتوسط اليومي</small>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
                            <canvas id="paidOrdersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Created Menafests Chart -->
            <div class="col-xl-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt text-success ms-2"></i>
                                المنافست المنشأة - آخر أسبوعين
                            </h5>
                            <span class="badge bg-success-light text-success px-3 py-2 rounded-pill">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ \Carbon\Carbon::now()->subDays(13)->format('Y-m-d') }} - {{ \Carbon\Carbon::now()->format('Y-m-d') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-around mb-4">
                            <div class="text-center">
                                <h3 class="text-success fw-bold mb-0">{{ array_sum($createdMenafestsChart['data']) }}</h3>
                                <small class="text-muted">إجمالي المنافست</small>
                            </div>
                            <div class="text-center">
                                <h3 class="text-primary fw-bold mb-0">{{ max($createdMenafestsChart['data']) }}</h3>
                                <small class="text-muted">أعلى يوم</small>
                            </div>
                            <div class="text-center">
                                <h3 class="text-info fw-bold mb-0">{{ number_format(array_sum($createdMenafestsChart['data']) / 14, 1) }}</h3>
                                <small class="text-muted">المتوسط اليومي</small>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
                            <canvas id="createdMenafestsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Your existing content here -->
    </div>
@endsection

@push('styles')
    <style>
        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .chart-container {
            background: white;
            border-radius: 16px;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Paid Orders Chart
            const paidOrdersCtx = document.getElementById('paidOrdersChart').getContext('2d');
            const paidOrdersData = @json($paidOrdersChart);

            const paidGradient = paidOrdersCtx.createLinearGradient(0, 0, 0, 350);
            paidGradient.addColorStop(0, 'rgba(79, 70, 229, 0.3)');
            paidGradient.addColorStop(0.5, 'rgba(79, 70, 229, 0.1)');
            paidGradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

            new Chart(paidOrdersCtx, {
                type: 'line',
                data: {
                    labels: paidOrdersData.labels,
                    datasets: [{
                        label: 'عدد الطلبات المدفوعة',
                        data: paidOrdersData.data,
                        borderColor: '#4f46e5',
                        backgroundColor: paidGradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#4f46e5',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            rtl: true,
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    family: 'Tajawal, sans-serif'
                                },
                                color: '#4b5563'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'عدد الطلبات: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    family: 'Tajawal, sans-serif'
                                },
                                color: '#6b7280',
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11,
                                    family: 'Tajawal, sans-serif'
                                },
                                color: '#6b7280',
                                callback: function(value) {
                                    if (Math.floor(value) === value) {
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                }
            });

            // Created Menafests Chart
            const menafestsCtx = document.getElementById('createdMenafestsChart').getContext('2d');
            const menafestsData = @json($createdMenafestsChart);

            const menafestsGradient = menafestsCtx.createLinearGradient(0, 0, 0, 350);
            menafestsGradient.addColorStop(0, 'rgba(40, 167, 69, 0.3)');
            menafestsGradient.addColorStop(0.5, 'rgba(40, 167, 69, 0.1)');
            menafestsGradient.addColorStop(1, 'rgba(40, 167, 69, 0)');

            new Chart(menafestsCtx, {
                type: 'line',
                data: {
                    labels: menafestsData.labels,
                    datasets: [{
                        label: 'عدد المنافست المنشأة',
                        data: menafestsData.data,
                        borderColor: '#28a745',
                        backgroundColor: menafestsGradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#28a745',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            rtl: true,
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    family: 'Tajawal, sans-serif'
                                },
                                color: '#4b5563'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'عدد المنافست: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    family: 'Tajawal, sans-serif'
                                },
                                color: '#6b7280',
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11,
                                    family: 'Tajawal, sans-serif'
                                },
                                color: '#6b7280',
                                callback: function(value) {
                                    if (Math.floor(value) === value) {
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush