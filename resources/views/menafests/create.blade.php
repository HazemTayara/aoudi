@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                @if($defaultType == 'outgoing')
                    إضافة منفست صادر (من المحلية)
                @else
                    إضافة منفست وارد (إلى المحلية)
                @endif
            </h2>
            <div>
                @if($defaultType == 'outgoing')
                    <a href="{{ route('menafests.outgoing') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للمنافست الصادرة
                    </a>
                @else
                    <a href="{{ route('menafests.incoming') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> العودة للمنافست الواردة
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('menafests.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        @if($defaultType == 'outgoing')
                            {{-- Outgoing: From City is Local (Static), To City is Selectable --}}
                            <div class="col-md-6 mb-3">
                                <label for="from_city_static" class="form-label">من مدينة <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" id="from_city_static"
                                    value="{{ $localCity->name }} (محلية)" readonly>
                                <input type="hidden" name="from_city_id" value="{{ $localCity->id }}">
                                <small class="text-muted">هذه هي المدينة المحلية (ثابت)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="to_city_id" class="form-label">إلى مدينة <span class="text-danger">*</span></label>
                                <select class="form-control @error('to_city_id') is-invalid @enderror" id="to_city_id"
                                    name="to_city_id" required>
                                    <option value="">اختر مدينة الوجهة</option>
                                    @foreach($toCities as $city)
                                        <option value="{{ $city->id }}" {{ old('to_city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            {{-- Incoming: To City is Local (Static), From City is Selectable --}}
                            <div class="col-md-6 mb-3">
                                <label for="from_city_id" class="form-label">من مدينة <span class="text-danger">*</span></label>
                                <select class="form-control @error('from_city_id') is-invalid @enderror" id="from_city_id"
                                    name="from_city_id" required>
                                    <option value="">اختر مدينة المصدر</option>
                                    @foreach($fromCities as $city)
                                        <option value="{{ $city->id }}" {{ old('from_city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="to_city_static" class="form-label">إلى مدينة <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" id="to_city_static"
                                    value="{{ $localCity->name }} (محلية)" readonly>
                                <input type="hidden" name="to_city_id" value="{{ $localCity->id }}">
                                <small class="text-muted">هذه هي المدينة المحلية (ثابت)</small>
                            </div>
                        @endif

                        <!-- Rest of the fields remain the same -->
                        <div class="col-md-6 mb-3">
                            <label for="manafest_code" class="form-label">كود المنفست <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('manafest_code') is-invalid @enderror"
                                id="manafest_code" name="manafest_code" value="{{ old('manafest_code') }}" required>
                            @error('manafest_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="driver_name" class="form-label">اسم السائق <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('driver_name') is-invalid @enderror"
                                id="driver_name" name="driver_name" value="{{ old('driver_name') }}" required>
                            @error('driver_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="car" class="form-label">السيارة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('car') is-invalid @enderror" id="car" name="car"
                                value="{{ old('car') }}" required>
                            @error('car')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">حفظ</button>
                </form>
            </div>
        </div>
    </div>
@endsection