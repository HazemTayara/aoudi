@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>السائقين</h2>
            <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة سائق
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم السائق</th>
                                <th>عدد الطلبات</th>
                                <th>ملاحظات</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers as $driver)
                                <tr>
                                    <td>{{ $driver->id }}</td>
                                    <td>{{ $driver->name }}</td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                                            {{ $driver->orders_count ?? $driver->orders()->count() }}
                                        </span>
                                    </td>
                                    <td>{{ $driver->notes ?? '—' }}</td>
                                    <td>{{ $driver->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('drivers.orders', $driver) }}"
                                                class="btn btn-sm btn-outline-success" title="إدارة الطلبات">
                                                <i class="fas fa-box"></i> الطلبات
                                            </a>
                                            <a href="{{ route('drivers.attach-orders', $driver) }}"
                                                class="btn btn-sm btn-outline-primary" title="إسناد طلبات">
                                                <i class="fas fa-link"></i> إسناد
                                            </a>
                                            <a href="{{ route('drivers.edit', $driver) }}"
                                                class="btn btn-sm btn-outline-secondary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا يوجد سائقين</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $drivers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection