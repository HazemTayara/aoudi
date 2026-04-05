@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>معاينة الطلبات المستوردة</h2>
        <a href="{{ route('menafests.orders.upload', $menafest) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> رفع ملف آخر
        </a>
    </div>

    @if(count($errors) > 0)
        <div class="alert alert-danger">
            <h5>الأخطاء التالية تم العثور عليها:</h5>
            <ul>
                @foreach($errors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <p>يرجى تصحيح الأخطاء وإعادة رفع الملف.</p>
        </div>
    @endif

    @if(count($orders) > 0)
        <div class="card mb-4">
            <div class="card-body">
                <h5>عدد الطلبات المستوردة: {{ count($orders) }}</h5>
                <form action="{{ route('menafests.orders.import', $menafest) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" {{ count($errors) > 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check"></i> تأكيد واستيراد الكل
                    </button>
                    <a href="{{ route('menafests.orders.index', $menafest) }}" class="btn btn-secondary">إلغاء</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>رقم الإيصال</th>
                                <th>النوع</th>
                                <th>العدد</th>
                                <th>المرسل</th>
                                <th>المرسل إليه</th>
                                <th>نوع الدفع</th>
                                <th>المبلغ</th>
                                <th>ضد الشحن</th>
                                <th>المحول</th>
                                <th>متفرقات</th>
                                <th>الخصم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $index => $order)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $order['order_number'] }}</td>
                                <td>{{ $order['content'] }}</td>
                                <td>{{ $order['count'] }}</td>
                                <td>{{ $order['sender'] }}</td>
                                <td>{{ $order['recipient'] }}</td>
                                <td>{{ $order['pay_type'] }}</td>
                                <td>{{ format_number($order['amount']) }}</td>
                                <td>{{ format_number($order['anti_charger']) }}</td>
                                <td>{{ format_number($order['transmitted']) }}</td>
                                <td>{{ format_number($order['miscellaneous']) }}</td>
                                <td>{{ format_number($order['discount']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            لا توجد طلبات صالحة للاستيراد. يرجى التحقق من الملف.
        </div>
    @endif
</div>
@endsection