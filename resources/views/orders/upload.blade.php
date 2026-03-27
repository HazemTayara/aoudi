@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>رفع ملف Excel للطلبات</h2>
            <a href="{{ route('menafests.orders.index', $menafest) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للطلبات
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @if ($menafest->fromCity->name == 'دمشق')
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        يرجى التأكد من أن ملف Excel يحتوي على الأعمدة التالية بالترتيب المناسب:
                        <ul class="mt-2">
                            <li><strong>الايصال</strong> - رقم الإيصال (إلزامي)</li>
                            <li><strong>النوع</strong> - محتوى الطرد</li>
                            <li><strong>العدد</strong> - عدد القطع (إلزامي)</li>
                            <li><strong>اسم المرسل</strong> - اسم المرسل (إلزامي)</li>
                            <li><strong>المرسل إليه</strong> - اسم المستلم (إلزامي)</li>
                            <li><strong>التحصيل</strong> - مبلغ التحصيل</li>
                            <li><strong>المدفوع مسبقا</strong> - المبلغ المدفوع مسبقاً</li>
                            <li><strong>ضد الشحن</strong> - مبلغ ضد الشاحن</li>
                            <li><strong>المحول</strong> - المبلغ المحول</li>
                            <li><strong>متفرقات متنوعة</strong> - مبلغ المتفرقات</li>
                            <li><strong>الخصم</strong> - مبلغ الخصم</li>
                        </ul>
                        <p>سيتم تحديد نوع الدفع تلقائياً بناءً على وجود قيمة في التحصيل أو المدفوع مسبقاً.</p>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        يرجى التأكد من أن ملف Excel يحتوي على الأعمدة التالية بالترتيب المناسب:
                        <ul class="mt-2">
                            <li><strong>المتسلسل</strong> - المتسلسل (إلزامي)</li>
                            <li><strong>رقم الاشعار</strong> - رقم الاشعار (إلزامي)</li>
                            <li><strong>نوع الطرد</strong> - محتوى الطرد</li>
                            <li><strong>الكمية</strong> - عدد القطع (إلزامي)</li>
                            <li><strong>المرسل</strong> - اسم المرسل (إلزامي)</li>
                            <li><strong>المرسل اليه</strong> - اسم المستلم (إلزامي)</li>
                            <li><strong>الصافي للدفع</strong> - قيمة الحوالة (إلزامي)</li>
                            <li><strong>الدفع</strong> - نوع الدفع (تحصيل - مسبق) </li>
                            <li><strong>ضد الدفع</strong> - مبلغ ضد الشاحن</li>
                            <li><strong>المحول</strong> - المبلغ المحول</li>
                            <li><strong>توصيل</strong> - مبلغ المتفرقات</li>
                        </ul>
                        <p>سيتم تحديد نوع الدفع تلقائياً بناءً على وجود قيمة في التحصيل أو المدفوع مسبقاً.</p>
                    </div>
                @endif

                <form action="{{ route('menafests.orders.preview', $menafest) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">اختر ملف Excel</label>
                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror" id="excel_file"
                            name="excel_file" accept=".xlsx,.xls,.csv" required>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">معاينة الطلبات</button>
                </form>
            </div>
        </div>
    </div>
@endsection