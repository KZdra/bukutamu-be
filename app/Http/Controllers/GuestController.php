<?php

namespace App\Http\Controllers;

use App\Exports\GuestExport;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GuestController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string',
            'photo' => 'required|image|max:2048',
        ]);
        $extension = $request->file('photo')->getClientOriginalExtension();
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->name);
        $timestamp = now()->format('Ymd_His'); // contoh: 20250704_143015
        $filename = $cleanName . '_foto_' . $timestamp . '.' . $extension;
        $photoPath = $request->file('photo')->storeAs('guest_pictures', $filename, 'public');
        try {
            Guest::create([
                'name' => ucwords($request->name),
                'institution' => $request->institution,
                'purpose' => $request->purpose,
                'photo_path' => str_replace('public/', 'storage/', $photoPath),
                'created_at' => Carbon::now()
            ]);
            return resJSON(1, "Tamu Berhasil Disimpan", ["nama_tamu" => ucwords($request->name)], 201);
        } catch (\Exception $e) {
            return resJSON(0, $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function GetAllGuest(Request $request)
    {
        $query = Guest::query();
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $guests = $query->orderBy('created_at', 'desc')->get()->map(function ($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'institution' => $guest->institution,
                'purpose' => $guest->purpose,
                'photo_url' => $guest->photo_url,
                'created_at' => Carbon::parse($guest->created_at)
                    ->locale('id')
                    ->translatedFormat('l, d F Y H:i'),
            ];
        });

        return resJSON(1, "Data GET", $guests, 200);
    }

    public function GetGuestById($id)
    {
        $guest = Guest::findOrFail($id);

        $data = [
            'id' => $guest->id,
            'name' => $guest->name,
            'institution' => $guest->institution,
            'purpose' => $guest->purpose,
            'photo_url' => $guest->photo_url,
            'created_at' => $guest->created_at,
        ];

        return resJSON(1, "Data GET", $data, 200);
    }

    public function getInstitutionList()
    {
        try {
            $institutions = DB::table('guests')
                ->select(DB::raw("LOWER(REPLACE(REPLACE(institution, 'PT.', 'PT'), '  ', ' ')) as instansi_normal ,REPLACE(REPLACE(institution, 'PT.', 'PT'), '  ', ' ') as instansi"))
                ->distinct()
                ->get()
                ->map(function ($ins) {
                    return [
                        "label" => $ins->instansi,
                        "value" => $ins->instansi_normal,
                    ];
                });
            return resJSON(1, "Data Get", $institutions, 200);
        } catch (\Exception $e) {
            return resJSON(0, "error", $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Guest $guest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function destroy(Guest $guest)
    {
        //
    }
    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filename = 'Laporan_Tamu.xlsx'; // Default filename

        if ($startDate && $endDate) {
            $filename = 'Laporan_Tamu_' . $startDate . '_' . $endDate . '.xlsx'; // Customize filename if start_date exists
        }

        // Use the Excel export
        return Excel::download(new GuestExport($startDate, $endDate), $filename);
    }
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filename = 'Laporan_Tamu.pdf'; // Default filename

        if ($startDate && $endDate) {
            $filename = 'Laporan_Tamu_' . $startDate . '_' . $endDate . '.pdf'; // Customize filename if start_date exists
        }
        $query = Guest::query();
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $guests = $query->orderBy('created_at', 'desc')->get()->map(function ($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'institution' => $guest->institution,
                'purpose' => $guest->purpose,
                'photo_url' => $guest->photo_url,
                'created_at' => Carbon::parse($guest->created_at)
                    ->locale('id')
                    ->translatedFormat('l, d F Y H:i'),
            ];
        });

        $pdf = Pdf::loadView('docs', [
            'guests' => $guests,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'now' => Carbon::parse(Carbon::now())->locale('id')
                ->translatedFormat('l, d F Y H:i')
        ]);
        return $pdf->stream($filename);
    }
}
