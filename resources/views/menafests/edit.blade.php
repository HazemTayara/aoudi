@extends('layouts.app')

@section('content')

    @php
        $type = $menafest->menafestType();
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>تعديل المنفست: {{ $menafest->manafest_code }}</h2>
            @if ($type == 'outgoing')
                <a href="{{ route('menafests.outgoing') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            @endif

            @if ($type == 'incoming')
                <a href="{{ route('menafests.incoming') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            @endif
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('menafests.update', $menafest) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        @if ($type == 'incoming')
                            <div class="col-md-6 mb-3">
                                <label for="from_city_id" class="form-label">من مدينة <span class="text-danger">*</span></label>
                                <select class="form-control @error('from_city_id') is-invalid @enderror" id="from_city_id"
                                    name="from_city_id" required>
                                    <option value="">اختر المدينة</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('from_city_id', $menafest->from_city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
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
                        @else
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
                                    <option value="">اختر المدينة</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('to_city_id', $menafest->to_city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                                @error('to_city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label for="manafest_code" class="form-label">كود المنفست <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('manafest_code') is-invalid @enderror"
                                id="manafest_code" name="manafest_code"
                                value="{{ old('manafest_code', $menafest->manafest_code) }}" required>
                            @error('manafest_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="driver_name" class="form-label">اسم السائق <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('driver_name') is-invalid @enderror"
                                id="driver_name" name="driver_name" value="{{ old('driver_name', $menafest->driver_name) }}"
                                required placeholder="أدخل اسم السائق">
                            @error('driver_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="car" class="form-label">السيارة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('car') is-invalid @enderror" id="car" name="car"
                                value="{{ old('car', $menafest->car) }}" required>
                            @error('car')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                rows="3">{{ old('notes', $menafest->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">تحديث</button>
                </form>
            </div>
        </div>
    </div>
@endsection