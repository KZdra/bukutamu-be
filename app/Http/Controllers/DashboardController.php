<?php

namespace App\Http\Controllers;

use App\Models\Guest;
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
    public function getLastTamu(Request $request)
    {
        $guests = Guest::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'institution' => $guest->institution,
                    'purpose' => $guest->purpose,
                    'photo_url' => $guest->photo_url,
                    'created_at' => $guest->created_at,
                ];
            });

        return resJSON(1, "get Data", $guests, 200);
    }
    public function getLastPerusahaan()
    {
        $institutions = DB::table('guests')
            ->distinct('institution')->get();
        return resJSON(1, "Get Data", $institutions, 200);
    }
}
