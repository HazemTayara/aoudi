<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\City;
use Illuminate\Support\Facades\DB;
class CheckOrderController extends Controller
{
    public function payIndex()
    {
        $localCity = City::where('is_local', true)->first();

        if (!$localCity) {
            return redirect()->route('settings.index')
                ->with('error', 'الرجاء تحديد المدينة المحلية أولاً من صفحة الإعدادات');
        }

        return view('check-orders.index', compact('localCity'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'number' => 'required|string'
        ]);

        $localCity = City::where('is_local', true)->first();

        // Search only in incoming orders (to_city_id is local city)
        $order = Order::with(['menafest.fromCity', 'menafest.toCity', 'driver'])
            ->where('order_number', $request->number)
            ->whereBetween('created_at', [now()->subDays(14), now()])
            ->whereHas('menafest', function ($query) use ($localCity) {
                $query->where('to_city_id', $localCity->id);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على طلب وارد بهذا الرقم'
            ]);
        }

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    public function markPaid(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $localCity = City::where('is_local', true)->first();

        // Find the order and verify it's incoming
        $order = Order::where('id', $request->order_id)
            ->whereHas('menafest', function ($query) use ($localCity) {
                $query->where('to_city_id', $localCity->id);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تحديث هذا الطلب (ليس طلب وارد)'
            ], 400);
        }

        if ($order->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب مدفوع بالفعل'
            ], 400);
        }

        $order->update([
            'is_paid' => true,
            'paid_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الدفع بنجاح',
            'order' => $order->load(['menafest.fromCity', 'menafest.toCity'])
        ]);
    }

    public function todayStats(Request $request)
    {
        $localCity = City::where('is_local', true)->first();

        $today = now()->format('Y-m-d');

        $total = Order::whereHas('menafest', function ($q) use ($localCity) {
            $q->where('to_city_id', $localCity->id);
        })
            ->whereDate('created_at', $today)
            ->count();

        $paid = Order::whereHas('menafest', function ($q) use ($localCity) {
            $q->where('to_city_id', $localCity->id);
        })
            ->whereDate('paid_at', $today)
            ->count();

        return response()->json([
            'total' => $total,
            'paid' => $paid,
            'remaining' => $total - $paid
        ]);
    }
}
