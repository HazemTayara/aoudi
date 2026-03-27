@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>المدن</h2>
            <a href="{{ route('cities.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة مدينة
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المدينة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cities as $city)
                            <tr>
                                <td>{{ $city->id }}</td>
                                <td>{{ $city->name }}</td>
                                <td>{{ $city->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('cities.edit', $city) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>

                                    @if (!$city->is_local)
                                        <a href="{{ route('cities.orders', $city) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-box"></i> عرض الطلبات
                                        </a>
                                    @endif
                                    {{-- <form action="{{ route('cities.destroy', $city) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('هل أنت متأكد؟')">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">لا توجد مدن</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center">
                    {{ $cities->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection