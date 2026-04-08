<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:super-admin')->only(['index', 'updateLocalCity']);

    }

    /**
     * Show the settings page
     */
    public function index()
    {
        $cities = City::all();
        $localCity = City::where('is_local', true)->first();

        return view('settings.index', compact('cities', 'localCity'));
    }

    /**
     * Update the local city setting
     */
    public function updateLocalCity(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id'
        ]);

        // Remove local flag from all cities
        City::query()->update(['is_local' => false]);

        // Set the new local city
        $city = City::find($request->city_id);
        $city->is_local = true;
        $city->save();

        return redirect()->route('settings.index')
            ->with('success', 'تم تحديث المدينة المحلية بنجاح');
    }
}