<?php
// app/Http/Controllers/DriverController.php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:restore-drivers')->only(['show']);
        $this->middleware('permission:create-drivers')->only(['create', 'store']);
        $this->middleware('permission:edit-drivers')->only(['edit', 'update']);
        $this->middleware('permission:delete-drivers')->only(['destroy']);
        $this->middleware('permission:force-delete-drivers')->only(['forceDelete']);
        $this->middleware('permission:restore-drivers')->only(['restore']);
    }

    public function index()
    {
        // Get only non-deleted drivers
        $drivers = Driver::withoutTrashed()->latest()->paginate(10);
        return view('drivers.index', compact('drivers'));
    }

    public function show()
    {
        $drivers = Driver::onlyTrashed()->latest('deleted_at')->paginate(10);
        return view('drivers.trashed', compact('drivers'));
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:drivers,name',
            'notes' => 'nullable|string'
        ]);

        Driver::create($request->all());

        return redirect()->route('drivers.index')
            ->with('success', 'تم إضافة السائق بنجاح');
    }

    public function edit(Driver $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:drivers,name,' . $driver->id,
            'notes' => 'nullable|string'
        ]);

        $driver->update($request->all());

        return redirect()->route('drivers.index')
            ->with('success', 'تم تحديث بيانات السائق بنجاح');
    }

    public function destroy(Driver $driver)
    {
        // Check if driver has orders
        if ($driver->hasOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف السائق لأنه مرتبط بطلبات'
            ], 400);
        }

        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السائق بنجاح'
        ]);
    }

    public function restore($id)
    {
        $driver = Driver::onlyTrashed()->findOrFail($id);
        $driver->restore();

        return response()->json([
            'success' => true,
            'message' => 'تم استعادة السائق بنجاح'
        ]);
    }

    public function forceDelete($id)
    {
        $driver = Driver::onlyTrashed()->findOrFail($id);

        // Double check no orders before permanent delete
        if ($driver->hasOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف السائق نهائياً لأنه مرتبط بطلبات'
            ], 400);
        }

        $driver->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السائق نهائياً'
        ]);
    }

    public function orders(Driver $driver)
    {
        $orders = $driver->orders()->latest()->paginate(20);
        return view('drivers.orders', compact('driver', 'orders'));
    }

    public function attachOrders(Driver $driver)
    {
        // This method will show form to attach orders to driver
        return view('drivers.attach-orders', compact('driver'));
    }
}