<?php

namespace App\Http\Controllers;

use App\Imports\OrdersPreviewImport;
use App\Models\Menafest;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderImportController extends Controller
{
    public function upload(Menafest $menafest)
    {
        return view('orders.upload', compact('menafest'));
    }

    public function preview(Request $request, Menafest $menafest)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        // Pass the menafest so the import knows which city format to use
        $import = new OrdersPreviewImport($menafest);
        Excel::import($import, $request->file('excel_file'));

        $orders = session('import_orders', []);
        $errors = session('import_errors', []);

        return view('orders.preview', compact('menafest', 'orders', 'errors'));
    }

    public function import(Request $request, Menafest $menafest)
    {
        $orders = session('import_orders', []);

        if (empty($orders)) {
            return redirect()->route('menafests.orders.upload', $menafest)
                ->with('error', 'لا توجد طلبات لاستيرادها. يرجى رفع ملف أولاً.');
        }

        foreach ($orders as $orderData) {
            $orderData['menafest_id'] = $menafest->id;
            Order::create($orderData);
        }

        session()->forget(['import_orders', 'import_errors']);

        return redirect()->route('menafests.orders.index', $menafest)
            ->with('success', 'تم استيراد ' . count($orders) . ' طلب بنجاح');
    }
}
