<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ManageOrderController extends Controller
{
    /**
     * Display a listing of all orders with server-side filtering and stats.
     */
    public function index(Request $request)
    {
        // getting incomming orders only
        $query = Order::with(['menafest.fromCity', 'menafest.toCity', 'driver'])
            ->incoming();

        // ─── Text search filters ───
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        if ($request->filled('content')) {
            $query->where('content', 'like', '%' . $request->input('content') . '%');
        }

        if ($request->filled('count')) {
            $query->where('count', $request->count);
        }

        if ($request->filled('sender')) {
            $query->where('sender', 'like', '%' . $request->sender . '%');
        }

        if ($request->filled('recipient')) {
            $query->where('recipient', 'like', '%' . $request->recipient . '%');
        }

        if ($request->filled('menafest_code')) {
            $query->whereHas('menafest', function ($q) use ($request) {
                $q->where('manafest_code', 'like', '%' . $request->menafest_code . '%');
            });
        }

        if ($request->filled('driver_name')) {
            $query->whereHas('driver', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->driver_name . '%');
            });
        }

        if ($request->filled('notes')) {
            $query->where('notes', 'like', '%' . $request->notes . '%');
        }

        // ─── Dropdown filters ───
        if ($request->filled('pay_type')) {
            $query->where('pay_type', $request->pay_type);
        }

        if ($request->has('is_paid') && $request->is_paid !== null && $request->is_paid !== '') {
            $query->where('is_paid', $request->is_paid);
        }

        if ($request->has('is_exist') && $request->is_exist !== null && $request->is_exist !== '') {
            $query->where('is_exist', $request->is_exist);
        }

        // ─── Numeric range filters ───
        $rangeFields = ['amount', 'anti_charger', 'transmitted', 'miscellaneous', 'discount'];

        foreach ($rangeFields as $field) {
            if ($request->filled("{$field}_min")) {
                $query->where($field, '>=', $request->input("{$field}_min"));
            }
            if ($request->filled("{$field}_max")) {
                $query->where($field, '<=', $request->input("{$field}_max"));
            }
        }

        // ─── Date range filters ───
        if ($request->filled('paid_from')) {
            $query->whereDate('paid_at', '>=', $request->paid_from);
        }

        if ($request->filled('paid_to')) {
            $query->whereDate('paid_at', '<=', $request->paid_to);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // ─── Compute stats from the filtered query (before pagination) ───
        $statsQuery = (clone $query);

        $stats = [
            'total_count' => $statsQuery->count(),
            'total_items' => (clone $statsQuery)->sum('count'),
            'total_amount' => (clone $statsQuery)->sum('amount'),
            'total_cash_amount' => (clone $statsQuery)->where('pay_type', 'تحصيل')->sum('amount'),
            'cash_count' => (clone $statsQuery)->where('pay_type', 'تحصيل')->count(),
            'total_prepaid_amount' => (clone $statsQuery)->where('pay_type', 'مسبق')->sum('amount'),
            'prepaid_count' => (clone $statsQuery)->where('pay_type', 'مسبق')->count(),
            'total_anti_charger' => (clone $statsQuery)->sum('anti_charger'),
            'anti_charger_count' => (clone $statsQuery)->where('anti_charger', '>', 0)->count(),
            'total_transmitted' => (clone $statsQuery)->sum('transmitted'),
            'transmitted_count' => (clone $statsQuery)->where('transmitted', '>', 0)->count(),
            'total_miscellaneous' => (clone $statsQuery)->sum('miscellaneous'),
            'miscellaneous_count' => (clone $statsQuery)->where('miscellaneous', '>', 0)->count(),
            'total_discount' => (clone $statsQuery)->sum('discount'),
            'discount_count' => (clone $statsQuery)->where('discount', '>', 0)->count(),
            'paid_count' => (clone $statsQuery)->where('is_paid', true)->count(),
            'unpaid_count' => (clone $statsQuery)->where('is_paid', false)->count(),
            'exist_count' => (clone $statsQuery)->where('is_exist', true)->count(),
        ];

        // ─── Paginate and preserve query parameters ───
        $orders = $query->latest()->paginate(25)->withQueryString();

        return view('manage-orders.index', compact('orders', 'stats'));
    }

    /**
     * Toggle the is_paid status of an order.
     */
    public function togglePaid(Order $order)
    {
        $order->is_paid = !$order->is_paid;
        $order->paid_at = $order->is_paid ? now() : null;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => $order->is_paid ? 'تم تحديث حالة الدفع: مدفوع' : 'تم تحديث حالة الدفع: غير مدفوع',
            'paid_at' => $order->paid_at ? $order->paid_at->format('Y-m-d H:i') : null,
        ]);
    }

    /**
     * Toggle the is_exist status of an order.
     */
    public function toggleExist(Order $order)
    {
        $order->is_exist = !$order->is_exist;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => $order->is_exist ? 'تم تحديث الحالة: موجود' : 'تم تحديث الحالة: غير موجود',
        ]);
    }

    /**
     * Update the notes of an order (inline edit).
     */
    public function updateNotes(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $order->notes = $request->notes;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الملاحظات بنجاح',
        ]);
    }
}
