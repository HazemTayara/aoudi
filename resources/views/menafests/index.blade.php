@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>{{ $pageTitle }}</h2>
            <div>
                @php 
                    $localCity = App\Models\City::where('is_local', true)->first();
                    $trashedCount = App\Models\Menafest::onlyTrashed()
                        ->when($type == 'incoming', function ($q) use ($localCity) {
                            return $q->where('to_city_id', $localCity->id);
                        })
                        ->when($type == 'outgoing', function ($q) use ($localCity) {
                            return $q->where('from_city_id', $localCity->id);
                        })
                        ->count();
                @endphp

                <a href="{{ $type == 'incoming' ? route('menafests.incoming.trashed') : route('menafests.outgoing.trashed') }}"
                    class="btn btn-warning me-2">
                    <i class="fas fa-trash-alt"></i> سلة المحذوفات
                    @if($trashedCount > 0)
                        <span class="badge bg-danger ms-1">{{ $trashedCount }}</span>
                    @endif
                </a>

                @if ($type == 'outgoing')
                    <a href="{{ route('menafests.create', ['type' => 'outgoing']) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة منفست صادر
                    </a>
                @endif

                @if ($type == 'incoming')
                    <a href="{{ route('menafests.create', ['type' => 'incoming']) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة منفست وارد
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- City Statistics Widget --}}
        @if(isset($cityStats) && count($cityStats) > 0)
            <div class="row g-3 mb-4">
                @foreach($cityStats as $cityName => $count)
                    <div class="col-md-2">
                        <div class="stat-card p-3 border rounded text-center">
                            <h4 class="stat-value-sm">{{ $cityName . ' : ' . $count }}</h4>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Search Filters --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-search"></i>
                    بحث وتصفية
                </h5>
            </div>
            <div class="card-body">
                <form method="GET"
                    action="{{ $type == 'incoming' ? route('menafests.incoming') : route('menafests.outgoing') }}"
                    id="filterForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="manafest_code" class="form-label">كود المنفست</label>
                            <input type="text" class="form-control" id="manafest_code" name="manafest_code"
                                value="{{ request('manafest_code') }}" placeholder="بحث بكود المنفست">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="city" class="form-label">
                                @if($type == 'incoming')
                                    المدينة المصدر
                                @else
                                    مدينة الوجهة
                                @endif
                            </label>
                            <input type="text" class="form-control" id="city" name="city" value="{{ request('city') }}"
                                placeholder="اسم المدينة">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="driver_name" class="form-label">اسم السائق</label>
                            <input type="text" class="form-control" id="driver_name" name="driver_name"
                                value="{{ request('driver_name') }}" placeholder="بحث باسم السائق">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="car" class="form-label">السيارة</label>
                            <input type="text" class="form-control" id="car" name="car" value="{{ request('car') }}"
                                placeholder="رقم أو نوع السيارة">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <input type="text" class="form-control" id="notes" name="notes" value="{{ request('notes') }}"
                                placeholder="بحث في الملاحظات">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="orders_count" class="form-label">عدد الطلبات</label>
                            <div class="input-group">
                                <select class="form-select" name="orders_count_operator" style="max-width: 80px;">
                                    <option value="equal" {{ request('orders_count_operator') == 'equal' ? 'selected' : '' }}>
                                        =</option>
                                    <option value="more" {{ request('orders_count_operator') == 'more' ? 'selected' : '' }}>></option>
                                    <option value="less" {{ request('orders_count_operator') == 'less' ? 'selected' : '' }}><</option>
                                </select>
                                <input type="number" class="form-control" id="orders_count" name="orders_count"
                                    value="{{ request('orders_count') }}" placeholder="العدد" min="0">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="date_from" class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="date_to" class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                value="{{ request('date_to') }}">
                        </div>

                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <a href="{{ $type == 'incoming' ? route('menafests.incoming') : route('menafests.outgoing') }}"
                                class="btn btn-secondary">
                                <i class="fas fa-redo"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>كود المنفست</th>
                                <th>مدينة</th>
                                <th>اسم السائق</th>
                                <th>السيارة</th>
                                <th>عدد الطلبات</th>
                                <th>ملاحظات</th>
                                <th>تاريخ الإضافة</th>
                                <th width="200">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menafests as $menafest)
                                <tr>
                                    <td>{{ $menafests->firstItem() + $loop->index }}</td>
                                    <td>{{ $menafest->manafest_code }}</td>
                                    @if($type == 'incoming')
                                        <td>{{ $menafest->fromCity->name }}</td>
                                    @else
                                        <td>{{ $menafest->toCity->name }}</td>
                                    @endif
                                    <td>{{ $menafest->driver_name }}</td>
                                    <td>{{ $menafest->car }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $menafest->orders->count() }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($menafest->notes, 30) ?? '—' }}</td>
                                    <td>{{ $menafest->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('menafests.edit', $menafest) }}"
                                                class="btn btn-sm btn-outline-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('menafests.orders.index', ['menafest' => $menafest, 'type' => $type]) }}"
                                                class="btn btn-sm btn-outline-secondary" title="عرض الطلبات">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-btn"
                                                data-id="{{ $menafest->id }}"
                                                data-code="{{ $menafest->manafest_code }}"
                                                data-type="{{ $type }}"
                                                data-orders-count="{{ $menafest->orders->count() }}"
                                                title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">لا يوجد منافست في هذا القسم</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($menafests->hasPages())
                    <div class="pagination-container">
                        <nav role="navigation" aria-label="Pagination Navigation">
                            <ul class="pagination">
                                {{-- Previous Page Link --}}
                                @if($menafests->onFirstPage())
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link prev-next">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link prev-next" href="{{ $menafests->previousPageUrl() }}" rel="prev">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- First page with ellipsis logic --}}
                                @if($menafests->currentPage() > 3)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $menafests->url(1) }}">1</a>
                                    </li>
                                    @if($menafests->currentPage() > 4)
                                        <li class="page-item disabled">
                                            <span class="page-link ellipsis">•••</span>
                                        </li>
                                    @endif
                                @endif

                                {{-- Pages around current page --}}
                                @foreach(range(1, $menafests->lastPage()) as $i)
                                    @if($i >= $menafests->currentPage() - 2 && $i <= $menafests->currentPage() + 2)
                                        @if($i == $menafests->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link active-page">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $menafests->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach

                                {{-- Last page with ellipsis logic --}}
                                @if($menafests->currentPage() < $menafests->lastPage() - 2)
                                    @if($menafests->currentPage() < $menafests->lastPage() - 3)
                                        <li class="page-item disabled">
                                            <span class="page-link ellipsis">•••</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $menafests->url($menafests->lastPage()) }}">
                                            {{ $menafests->lastPage() }}
                                        </a>
                                    </li>
                                @endif

                                {{-- Next Page Link --}}
                                @if($menafests->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link prev-next" href="{{ $menafests->nextPageUrl() }}" rel="next">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link prev-next">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .gap-2 {
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            min-width: 35px;
        }
        
        .d-flex {
            display: flex;
            flex-wrap: wrap;
        }
        
        /* Pagination Styles with Rounded Borders */
        :root {
            --pagination-radius: 14px;
            --pagination-glow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            --pagination-transition: all 0.2s ease;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            padding: 1rem 0;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Page Items */
        .page-item {
            margin: 0;
        }

        /* Page Links - Base Style */
        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 0.9rem;
            background: transparent;
            color: var(--heading-color);
            text-decoration: none;
            border-radius: var(--pagination-radius);
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--pagination-transition);
            border: 2px solid transparent;
            cursor: pointer;
        }

        /* Unselected Pages */
        .page-link:not(.active-page):not(.prev-next):not(.ellipsis) {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        /* Hover State for Unselected Pages */
        .page-link:not(.active-page):not(.prev-next):not(.ellipsis):hover {
            background: var(--accent-color);
            color: #4a5568;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(99, 102, 241, 0.25);
        }

        /* Active Page - Selected State */
        .page-item.active .page-link,
        .page-link.active-page {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(99, 102, 241, 0.3);
            font-weight: 700;
        }

        /* Previous/Next Buttons */
        .page-link.prev-next {
            background: white;
            border: 2px solid #e2e8f0;
            min-width: 42px;
            padding: 0;
            border-radius: var(--pagination-radius);
        }

        .page-link.prev-next:hover:not(.disabled .page-link) {
            transform: translateY(-2px);
        }

        /* Disabled State */
        .page-item.disabled .page-link {
            background: #f7fafc;
            color: #a0aec0;
            border-color: #e2e8f0;
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            pointer-events: none;
        }

        /* Ellipsis Style */
        .page-link.ellipsis {
            background: transparent;
            border: none;
            color: #a0aec0;
            min-width: auto;
            padding: 0 0.25rem;
            font-size: 1.1rem;
            letter-spacing: 2px;
            cursor: default;
            pointer-events: none;
        }

        .page-link.ellipsis:hover {
            background: transparent;
            transform: none;
            box-shadow: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .pagination {
                gap: 0.35rem;
            }

            .page-link {
                min-width: 38px;
                height: 38px;
                padding: 0 0.7rem;
                font-size: 0.9rem;
                border-radius: 12px;
            }

            .page-link.prev-next {
                min-width: 38px;
            }
            
            .btn-sm {
                min-width: 30px;
                padding: 0.3rem 0.6rem;
            }
        }

        @media (max-width: 480px) {
            .pagination {
                gap: 0.25rem;
            }

            .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0 0.5rem;
                font-size: 0.85rem;
                border-radius: 10px;
            }
        }

        /* Focus State for Accessibility */
        .page-link:focus-visible {
            outline: none;
            box-shadow: var(--pagination-glow);
            border-color: var(--accent-color);
        }

        /* Selected page animation */
        .page-item.active .page-link {
            animation: pop 0.2s ease;
        }

        @keyframes pop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.08);
            }

            100% {
                transform: scale(1) translateY(-2px);
            }
        }
    </style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    
    // Delete button handler
    $(document).on('click', '.delete-btn', function() {
        const menafestId = $(this).data('id');
        const menafestCode = $(this).data('code');
        const type = $(this).data('type');
        const ordersCount = $(this).data('orders-count');
        
        let warningMessage = '';
        if (ordersCount > 0) {
            warningMessage = `<p class="text-danger mt-2"><strong>⚠️ تنبيه:</strong> هذا المنفست يحتوي على <strong>${ordersCount}</strong> طلب(طلبات) وسيتم حذفها معه!</p>`;
        }
        
        Swal.fire({
            title: 'هل أنت متأكد؟',
            html: `
                هل تريد حذف المنفست <strong>${menafestCode}</strong>؟
                ${warningMessage}
                <small class="text-muted d-block mt-2">📦 سيتم نقل المنفست والطلبات المرتبطة به إلى سلة المحذوفات</small>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'جاري الحذف...',
                    text: 'يرجى الانتظار',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: `/menafests/${menafestId}`,
                    type: 'DELETE',
                    data: {
                        type: type,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الحذف',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'حدث خطأ أثناء حذف المنفست';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: errorMessage,
                            confirmButtonText: 'حسنًا'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush