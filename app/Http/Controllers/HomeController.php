<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Material;
use App\Models\Menafest;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;

use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Lang;
use Log;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $paidOrdersChart = $this->getPaidOrdersChartData();
        $createdMenafestsChart = $this->getCreatedMenafestsChartData();

        return view('home', compact('paidOrdersChart', 'createdMenafestsChart'));
    }

    private function getPaidOrdersChartData()
    {
        $data = [];
        $labels = [];
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(13); // Last 14 days (2 weeks)

        // Set Carbon locale to Arabic

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $count = Order::whereDate('paid_at', $date->format('Y-m-d'))
                ->where('is_paid', true)
                ->count();

            $data[] = $count;
            $labels[] = $date->translatedFormat('D d/m'); // Arabic day name + date
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }


    private function getCreatedMenafestsChartData()
    {
        $data = [];
        $labels = [];
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(13); // Last 14 days (2 weeks)

        Carbon::setLocale('ar');

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $count = Menafest::whereDate('created_at', $date->format('Y-m-d'))
                ->count();

            $data[] = $count;
            $labels[] = $date->translatedFormat('D d/m'); // Arabic day name + date
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
