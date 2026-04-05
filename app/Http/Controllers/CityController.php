<?php
// app/Http/Controllers/CityController.php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use App\Models\Order;

class CityController extends Controller
{
    public function index(Request $request) // Add Request here
    {
        // Get only non-local and non-deleted cities
        $cities = City::where('is_local', false)
            ->withoutTrashed()
            ->latest()
            ->paginate(10);

        return view('cities.index', compact('cities'));
    }

    public function orders(City $city, Request $request)
    {
        // getting incoming orders only
        $query = Order::with(['menafest.fromCity', 'menafest.toCity', 'driver'])
            ->incoming()
            ->whereHas('menafest.fromCity', function ($q) use ($city) {
                $q->where('name', $city->name);
            });

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

        return view('cities.orders', compact('orders', 'stats', 'city'));
    }

    public function create()
    {
        return view('cities.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:cities,name']);

        City::create($request->all());

        return redirect()->route('cities.index')->with('success', 'تم إضافة المدينة بنجاح');
    }

    public function edit(City $city)
    {
        // Prevent editing local city
        if ($city->is_local) {
            return redirect()->route('cities.index')->with('error', 'لا يمكن تعديل المدينة الرئيسية');
        }

        return view('cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $request->validate(['name' => 'required|string|max:255|unique:cities,name,' . $city->id]);

        $city->update($request->all());

        return redirect()->route('cities.index')->with('success', 'تم تحديث المدينة بنجاح');
    }

    public function destroy(City $city)
    {
        // Check if city has orders
        if ($city->hasOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المدينة لأنها تحتوي على طلبات'
            ], 400);
        }

        // Check if it's local city
        if ($city->is_local) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المدينة الرئيسية'
            ], 400);
        }

        $city->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المدينة بنجاح'
        ]);
    }

    public function show()
    {
        $cities = City::onlyTrashed()->latest()->paginate(10);
        return view('cities.trashed', compact('cities'));
    }

    public function restore($id)
    {
        $city = City::onlyTrashed()->findOrFail($id);
        $city->restore();

        return response()->json([
            'success' => true,
            'message' => 'تم استعادة المدينة بنجاح'
        ]);
    }

    public function forceDelete($id)
    {
        $city = City::onlyTrashed()->findOrFail($id);

        // Double check no orders before permanent delete
        if ($city->hasOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المدينة نهائياً لأنها تحتوي على طلبات'
            ], 400);
        }

        $city->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المدينة نهائياً'
        ]);
    }
}