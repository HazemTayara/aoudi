<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Log;

class DriverOrderController extends Controller
{
    /**
     * Manage orders belonging to a driver (with server-side filters)
     */
    public function index(Driver $driver, Request $request)
    {
        // Base query with all filters
        $baseQuery = $driver->orders()->with('menafest.fromCity', 'menafest.toCity');

        // Apply status filter first unpaid to optimize query
        if ($request->has('status')) {
            if ($request->status === 'undelivered') {
                $baseQuery->where('is_paid', false);
            }
        }

        // Apply all filters to base query
        $this->applyFilters($baseQuery, $request);

        // Clone the query for stats before pagination
        $statsQuery = clone $baseQuery;

        // Get paginated orders
        $orders = $baseQuery->latest('assigned_at')->paginate(50)->appends($request->query());

        // Calculate stats from the full filtered result
        $stats = [
            'total' => $statsQuery->count(),
            'paid_count' => (clone $statsQuery)->where('is_paid', true)->count(),
            'unpaid_count' => (clone $statsQuery)->where('is_paid', false)->count(),
            'exist_count' => (clone $statsQuery)->where('is_exist', true)->count(),
            'total_amount' => (clone $statsQuery)->sum('amount'),
            'collection_amount' => (clone $statsQuery)->where('pay_type', 'تحصيل')->sum('amount'),
        ];

        return view('drivers.orders', compact('driver', 'orders', 'stats'));
    }

    // Helper method to apply filters
    private function applyFilters($query, Request $request)
    {
        // Filter: assigned_at date range
        if ($request->filled('assigned_from')) {
            $query->whereDate('assigned_at', '>=', $request->assigned_from);
        }

        if ($request->filled('assigned_to')) {
            $query->whereDate('assigned_at', '<=', $request->assigned_to);
        }

        // Filter: created_at date range
        if ($request->filled('created_from')) {
            $query->whereDate('orders.created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('orders.created_at', '<=', $request->created_to);
        }

        // Filter: paid_at date range
        if ($request->filled('paid_from')) {
            $query->whereDate('paid_at', '>=', $request->paid_from);
        }
        if ($request->filled('paid_to')) {
            $query->whereDate('paid_at', '<=', $request->paid_to);
        }

        // Filter: is_paid
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->is_paid);
        }

        // Filter: is_exist
        if ($request->filled('is_exist')) {
            $query->where('is_exist', $request->is_exist);
        }

        // Filter: pay_type
        if ($request->filled('pay_type')) {
            $query->where('pay_type', $request->pay_type);
        }

        // Search: order_number
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        // Search: sender
        if ($request->filled('sender')) {
            $query->where('sender', 'like', '%' . $request->sender . '%');
        }

        // Search: recipient
        if ($request->filled('recipient')) {
            $query->where('recipient', 'like', '%' . $request->recipient . '%');
        }

        return $query;
    }

    /**
     * Show form to attach unassigned orders to a driver
     */
    public function attachForm(Driver $driver, Request $request)
    {
        $query = Order::whereNull('driver_id')
            ->where('is_paid', false)
            ->where('is_exist', true)
            ->with('menafest.fromCity', 'menafest.toCity')->incoming();

        // Search: order_number
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        // Search: sender
        if ($request->filled('sender')) {
            $query->where('sender', 'like', '%' . $request->sender . '%');
        }

        // Search: recipient
        if ($request->filled('recipient')) {
            $query->where('recipient', 'like', '%' . $request->recipient . '%');
        }

        // Filter: pay_type
        if ($request->filled('pay_type')) {
            $query->where('pay_type', $request->pay_type);
        }

        $orders = $query->latest()->paginate(50)->appends($request->query());

        return view('drivers.attach', compact('driver', 'orders'));
    }

    /**
     * Attach selected orders to the driver
     */
    public function attach(Request $request, Driver $driver)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $now = Carbon::now();

            Order::where('id', $request->order_id)
                ->whereNull('driver_id')
                ->update([
                    'driver_id' => $driver->id,
                    'assigned_at' => $now,
                ]);



            return redirect()->route('drivers.attach-orders', $driver)
                ->with('success', "تم إسناد للسائق {$driver->name} بنجاح");
        } catch (\Throwable $th) {
            Log::error('Error attaching orders to driver: ' . $th->getMessage(), [
                'driver_id' => $driver->id,

            ]);
        }
    }

    /**
     * Detach an order from the driver
     */
    public function detach(Driver $driver, Order $order)
    {
        if ($order->driver_id !== $driver->id) {
            return response()->json(['success' => false, 'message' => 'هذا الطلب لا ينتمي لهذا السائق'], 403);
        }

        $order->update([
            'driver_id' => null,
            'assigned_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم فك إسناد الطلب بنجاح',
        ]);
    }

    /**
     * Toggle is_paid for a driver's order
     */
    public function toggleIsPaid(Order $order)
    {
        $order->is_paid = !$order->is_paid;
        $order->paid_at = $order->is_paid ? Carbon::now() : null;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الدفع بنجاح',
            'is_paid' => $order->is_paid,
            'paid_at' => $order->paid_at ? $order->paid_at->format('Y-m-d H:i') : null,
        ]);
    }

    /**
     * Toggle is_exist for a driver's order
     */
    public function toggleIsExist(Order $order)
    {
        $order->is_exist = !$order->is_exist;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الوجود بنجاح',
            'is_exist' => $order->is_exist,
        ]);
    }

    /**
     * Update notes for a driver's order (inline edit)
     */
    public function updateNotes(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $order->update(['notes' => $request->notes]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملاحظات بنجاح',
            'notes' => $order->notes,
        ]);
    }
}
