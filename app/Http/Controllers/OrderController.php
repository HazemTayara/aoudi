<?php

namespace App\Http\Controllers;

use App\Models\Menafest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display orders for a specific menafest
     */
    public function index(Menafest $menafest, Request $request)
    {
        // Get all orders for this menafest
        $orders = $menafest->orders()->orderBy('created_at', 'desc')->get();

        return view('orders.index', compact('menafest', 'orders'));
    }

    public function trashedOrders(Menafest $menafest)
    {
        $orders = $menafest->orders()->onlyTrashed()->latest('deleted_at')->paginate(25);
        $stats = [];

        return view('orders.trashed', compact('menafest', 'orders', 'stats'));
    }

    /**
     * Store a new order
     */
    public function store(Request $request, Menafest $menafest)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255',
            'content' => 'nullable|string|max:255',
            'count' => 'required|integer|min:1',
            'sender' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'pay_type' => 'required|in:مسبق,تحصيل',
            'amount' => 'nullable|numeric|min:0',
            'anti_charger' => 'nullable|numeric|min:0',
            'transmitted' => 'nullable|numeric|min:0',
            'miscellaneous' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Set defaults
        $validated['amount'] = $validated['amount'] ?? 0;
        $validated['anti_charger'] = $validated['anti_charger'] ?? 0;
        $validated['transmitted'] = $validated['transmitted'] ?? 0;
        $validated['miscellaneous'] = $validated['miscellaneous'] ?? 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['menafest_id'] = $menafest->id;

        $order = Order::create($validated);

        // Load any relationships if needed
        $order->load('menafest');

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الطلب بنجاح',
            'order' => $order
        ]);
    }


    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));

    }

    /**
     * Update order
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255',
            'content' => 'nullable|string|max:255',
            'count' => 'required|integer|min:1',
            'sender' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'pay_type' => 'required|in:مسبق,تحصيل',
            'amount' => 'required|numeric|min:0',
            'anti_charger' => 'nullable|numeric|min:0',
            'transmitted' => 'nullable|numeric|min:0',
            'miscellaneous' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Set defaults
        $validated['anti_charger'] = $validated['anti_charger'] ?? 0;
        $validated['transmitted'] = $validated['transmitted'] ?? 0;
        $validated['miscellaneous'] = $validated['miscellaneous'] ?? 0;
        $validated['discount'] = $validated['discount'] ?? 0;

        $order->update($validated);

        return redirect()->route('orders.edit', $order)->with('success', 'تم تحديث الطلب بنجاح');
    }

    public function toggleIsPaid(Order $order)
    {
        $order->is_paid = !$order->is_paid;
        $order->save();

        return redirect()->back()->with('success', 'تم تحديث حالة الدفع بنجاح');
    }

    public function toggleIsExist(Order $order)
    {
        $order->is_exist = !$order->is_exist;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة وجود الطلب بنجاح',
            'is_exist' => $order->is_exist
        ]);
    }
}