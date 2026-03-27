<?php

namespace App\Http\Controllers;

use App\Models\Menafest;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ManifestOutgoingExport;
use Log;
use Maatwebsite\Excel\Facades\Excel;
class MenafestController extends Controller
{
    /**
     * Display incoming manifests (to local city)
     */
    public function incoming(Request $request)
    {
        $localCity = City::where('is_local', true)->first();

        if (!$localCity) {
            return redirect()->route('settings.index')
                ->with('error', 'الرجاء تحديد المدينة المحلية أولاً من صفحة الإعدادات');
        }

        $query = Menafest::with(['fromCity', 'toCity', 'orders'])
            ->where('to_city_id', $localCity->id);

        // Apply filters
        $query = $this->applyFilters($query, $request, 'incoming');

        $menafests = $query->latest()->paginate(10)->withQueryString();

        // Get city statistics for incoming (group by from_city)
        $cityStats = $this->applyFilters(Menafest::where('to_city_id', $localCity->id)
            ->with('fromCity')
            ->select('from_city_id', DB::raw('count(*) as total'))
            ->groupBy('from_city_id'), $request, 'incoming');

        $cityStats = $cityStats->get()->mapWithKeys(function ($item) {
            return [$item->fromCity->name => $item->total];
        });

        $type = 'incoming';
        $pageTitle = 'منافست وارد';

        return view('menafests.index', compact('menafests', 'type', 'pageTitle', 'localCity', 'cityStats'));
    }

    /**
     * Display outgoing manifests (from local city)
     */
    public function outgoing(Request $request)
    {
        $localCity = City::where('is_local', true)->first();

        if (!$localCity) {
            return redirect()->route('settings.index')
                ->with('error', 'الرجاء تحديد المدينة المحلية أولاً من صفحة الإعدادات');
        }

        $query = Menafest::with(['fromCity', 'toCity', 'orders'])
            ->where('from_city_id', $localCity->id);

        // Apply filters
        $query = $this->applyFilters($query, $request, 'outgoing');

        $menafests = $query->latest()->paginate(10)->withQueryString();

        // Get city statistics for outgoing (group by to_city)
        $cityStats = Menafest::where('from_city_id', $localCity->id)
            ->with('toCity')
            ->select('to_city_id', DB::raw('count(*) as total'))
            ->groupBy('to_city_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->toCity->name => $item->total];
            });

        $type = 'outgoing';
        $pageTitle = 'منافست صادر';

        return view('menafests.index', compact('menafests', 'type', 'pageTitle', 'localCity', 'cityStats'));
    }

    public function exportOutgoing(Menafest $menafest)
    {
        // Verify it's an outgoing manifest
        $localCity = City::where('is_local', true)->first();

        if ($menafest->menafestType() == 'incoming') {
            return redirect()->back()->with('error', 'هذا المنفست ليس منفست صادر');
        }

        // Load the manifest with its orders and cities
        $menafest->load(['fromCity', 'toCity', 'orders']);

        $fileName = $menafest->toCity->name . '-' . $menafest->manafest_code . '.xlsx';

        return Excel::download(new ManifestOutgoingExport($menafest), $fileName);
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, $request, $type)
    {
        // Search by manifest code
        if ($request->filled('manafest_code')) {
            $query->where('manafest_code', 'like', '%' . $request->manafest_code . '%');
        }

        // Search by city (from_city for incoming, to_city for outgoing)
        if ($request->filled('city')) {
            if ($type == 'incoming') {
                $query->whereHas('fromCity', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->city . '%');
                });
            } else {
                $query->whereHas('toCity', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->city . '%');
                });
            }
        }

        // Search by driver name
        if ($request->filled('driver_name')) {
            $query->where('driver_name', 'like', '%' . $request->driver_name . '%');
        }

        // Search by car
        if ($request->filled('car')) {
            $query->where('car', 'like', '%' . $request->car . '%');
        }

        // Search by notes
        if ($request->filled('notes')) {
            $query->where('notes', 'like', '%' . $request->notes . '%');
        }

        // Filter by orders count
        if ($request->filled('orders_count')) {
            if ($request->orders_count_operator == 'more') {
                $query->has('orders', '>', $request->orders_count);
            } elseif ($request->orders_count_operator == 'less') {
                $query->has('orders', '<', $request->orders_count);
            } else {
                $query->has('orders', '=', $request->orders_count);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Show form for creating a new manifest
     */
    public function create(Request $request)
    {
        $localCity = City::where('is_local', true)->first();

        if (!$localCity) {
            return redirect()->route('settings.index')
                ->with('error', 'الرجاء تحديد المدينة المحلية أولاً من صفحة الإعدادات');
        }

        $defaultType = $request->get('type', 'outgoing');

        // Filter cities based on manifest type
        if ($defaultType == 'outgoing') {
            // For outgoing: from_city MUST be local, to_city CANNOT be local
            $fromCities = City::where('id', $localCity->id)->get();
            $toCities = City::where('is_local', false)->get();
        } else {
            // For incoming: from_city CANNOT be local, to_city MUST be local
            $fromCities = City::where('is_local', false)->get();
            $toCities = City::where('id', $localCity->id)->get();
        }

        return view('menafests.create', compact(
            'localCity',
            'defaultType',
            'fromCities',
            'toCities'
        ));
    }

    /**
     * Store a newly created manifest
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_city_id' => 'required|exists:cities,id',
            'to_city_id' => 'required|exists:cities,id|different:from_city_id',
            'manafest_code' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'car' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $localCity = City::where('is_local', true)->first();

        // Redirect based on manifest type
        $menafest = Menafest::create($request->all());

        return redirect()->route('menafests.orders.index', $menafest)
            ->with('success', 'تم إضافة المنفست بنجاح');
    }

    /**
     * Show form for editing a manifest
     */
    public function edit(Menafest $menafest)
    {
        $cities = City::all();
        $localCity = City::where('is_local', true)->first();
        return view('menafests.edit', compact('menafest', 'cities', 'localCity'));
    }

    /**
     * Update the specified manifest
     */
    public function update(Request $request, Menafest $menafest)
    {
        $request->validate([
            'from_city_id' => 'required|exists:cities,id',
            'to_city_id' => 'required|exists:cities,id|different:from_city_id',
            'manafest_code' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'car' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $menafest->update($request->all());

        $localCity = City::where('is_local', true)->first();

        // Redirect based on manifest type
        if ($menafest->from_city_id == $localCity->id) {
            return redirect()->route('menafests.outgoing')
                ->with('success', 'تم تحديث المنفست الصادر بنجاح');
        } else {
            return redirect()->route('menafests.incoming')
                ->with('success', 'تم تحديث المنفست الوارد بنجاح');
        }
    }

    /**
     * Remove the specified manifest
     */
    // public function destroy(Menafest $menafest)
    // {
    //     $localCity = City::where('is_local', true)->first();
    //     $wasOutgoing = ($menafest->from_city_id == $localCity->id);

    //     $menafest->delete();

    //     if ($wasOutgoing) {
    //         return redirect()->route('menafests.outgoing')
    //             ->with('success', 'تم حذف المنفست الصادر بنجاح');
    //     } else {
    //         return redirect()->route('menafests.incoming')
    //             ->with('success', 'تم حذف المنفست الوارد بنجاح');
    //     }
    // }
}