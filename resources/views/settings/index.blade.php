@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>الإعدادات</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">تحديد المدينة المحلية</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            المدينة المحلية هي المركز الرئيسي للعمليات. سيتم استخدامها لتصنيف المنافست إلى:
                        </p>
                        <ul class="mb-3">
                            <li><strong>منافست صادر:</strong> من المدينة المحلية إلى مدن أخرى</li>
                            <li><strong>منافست وارد:</strong> من مدن أخرى إلى المدينة المحلية</li>
                        </ul>

                        @if($localCity)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                المدينة المحلية الحالية: <strong>{{ $localCity->name }}</strong>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                لم يتم تحديد مدينة محلية بعد. الرجاء اختيار مدينة محلية.
                            </div>
                        @endif

                        <form action="{{ route('settings.local-city.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="city_id" class="form-label">اختر المدينة المحلية</label>
                                <select class="form-control @error('city_id') is-invalid @enderror" id="city_id"
                                    name="city_id" required>
                                    <option value="">-- اختر مدينة --</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ $localCity && $localCity->id == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ الإعدادات
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Additional settings can be added here later -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات النظام</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>عدد المدن</th>
                                <td>{{ $cities->count() }}</td>
                            </tr>
                            <tr>
                                <th>حالة المدينة المحلية</th>
                                <td>
                                    @if($localCity)
                                        <span class="badge bg-success">محددة: {{ $localCity->name }}</span>
                                    @else
                                        <span class="badge bg-danger">غير محددة</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection