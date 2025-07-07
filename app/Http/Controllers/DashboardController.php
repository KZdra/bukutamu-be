<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function getCardData(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $jumlahHariIni = DB::table('guests')
            ->whereDate('created_at', $today)
            ->count();

        $jumlahMingguIni = DB::table('guests')
            ->whereBetween('created_at', [$startOfWeek, now()])
            ->count();

        $jumlahBulanIni = DB::table('guests')
            ->whereBetween('created_at', [$startOfMonth, now()])
            ->count();

        $jumlahInstansiBerbeda = DB::table('guests')
            ->distinct('institution')
            ->count('institution');
        $data = [
            'today' => $jumlahHariIni,
            'this_week' => $jumlahMingguIni,
            'this_month' => $jumlahBulanIni,
            'unique_institution' => $jumlahInstansiBerbeda,
        ];
        return resJSON(1, "Get Data", $data, 200);
    }
}
