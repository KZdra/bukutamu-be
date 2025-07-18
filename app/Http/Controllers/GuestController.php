<?php

namespace App\Http\Controllers;

use App\Exports\GuestExport;
use App\Models\Guest;
use App\Models\Status;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GuestController extends Controller
{


    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'purpose' => 'required|string',
            'unit_id' => 'required|exists:units,id',
            'id_card_number' => 'nullable|string|max:100',
            'type' => 'required|in:perorangan,badan_usaha',
            'institution' => 'string|max:255',
            'institution_address' => 'string',
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
                'phone' => $request->phone,
                'purpose' => $request->purpose,
                'unit_id' => $request->unit_id,
                'id_card_number' => $request->id_card_number,
                'type' => $request->type,
                'institution' => $request->institution,
                'institution_address' => $request->institution_address,
                'photo_path' => str_replace('public/', 'storage/', $photoPath),
                'status_id' => 1,
                'created_at' => Carbon::now()
            ]);
            return resJSON(1, "Tamu Berhasil Disimpan", ["nama_tamu" => ucwords($request->name)], 201);
        } catch (\Exception $e) {
            return resJSON(0, $e->getMessage(), [], 500);
        }

    }
    public function getallStatus()
    {
        $status = Status::select('id', 'name')->orderBy('id')->get()->map(function ($status) {
            return [
                'value' => $status->id,
                'label' => $status->name
            ];
        });

        return resJSON(1, "DATA GET", $status, 200);
    }
    public function getAllUnit()
    {
        $units = Unit::select('id', 'name', 'unit_phone')->orderBy('name')->get()->map(function ($units) {
            return [
                'value' => $units->id,
                'label' => $units->name
            ];
        });

        return resJSON(1, "DATA GET", $units, 200);
    }

    public function GetAllGuest(Request $request)
    {
        $query = Guest::with('status', 'unit');
    
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
    
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%$search%")
                    ->orWhere('phone', 'ilike', "%$search%")
                    ->orWhere('id_card_number', 'ilike', "%$search%")
                    ->orWhere('purpose', 'ilike', "%$search%")
                    ->orWhere('institution', 'ilike', "%$search%")
                    ->orWhere('institution_address', 'ilike', "%$search%");
            });
        }
                
        $perPage = $request->get('limit', 10); // default 10, bisa diubah dari frontend
        $paginated = $query->orderBy('created_at', 'desc')->paginate($perPage);
    
        // Format data untuk setiap item
        $items = $paginated->getCollection()->map(function ($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'guest_phone' => $guest->phone,
                'id_card_number' => $guest->id_card_number,
                'type' => ucwords($guest->type),
                'institution' => $guest->institution,
                'institution_address' => $guest->institution_address,
                'purpose' => $guest->purpose,
                'photo_url' => $guest->photo_url,
                'status' => $guest->status?->name,
                'unit' => $guest->unit?->name,
                'created_at' => Carbon::parse($guest->created_at)
                    ->locale('id')
                    ->translatedFormat('l, d F Y H:i'),
            ];
        });
    
        return response()->json([
            'data' => $items,
            'total' => $paginated->total(),
            'per_page' => $paginated->perPage(),
            'current_page' => $paginated->currentPage(),
        ]);
    }
    
    public function GetGuestById($id)
    {
        $guest = Guest::with('status', 'unit')->findOrFail($id);

        $data = [
            'id' => $guest->id,
            'name' => $guest->name,
            'guest_phone' => $guest->phone,
            'id_card_number' => $guest->id_card_number,
            'type' => ucwords($guest->type),
            'institution' => $guest->institution,
            'institution_address' => $guest->institution_address,
            'purpose' => $guest->purpose,
            'photo_url' => $guest->photo_url,
            'status' => $guest->status?->name,
            'unit' => $guest->unit?->name,
            'created_at' => Carbon::parse($guest->created_at)
                ->locale('id')
                ->translatedFormat('l, d F Y H:i'),
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


    public function update(Request $request, Guest $guest)
    {
        //
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusId = $request->input('status_id');
        $unitID = $request->input('unit_id');
        $filename = 'Laporan_Tamu.xlsx';

        if ($startDate && $endDate) {
            $filename = 'Laporan_Tamu_' . $startDate . '_' . $endDate . '.xlsx';
        }

        return Excel::download(new GuestExport($startDate, $endDate, $statusId, $unitID), $filename);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusId = $request->input('status_id');
        $unitId = $request->input('unit_id');

        $filename = 'Laporan_Tamu.pdf'; // Default filename

        if ($startDate && $endDate) {
            $filename = 'Laporan_Tamu_' . $startDate . '_' . $endDate . '.pdf'; // Customize filename if start_date exists
        }
        $query = Guest::with('status','unit');
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $statusId);
        }
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $unitId);
        }
        $guests = $query->orderBy('created_at', 'desc')->get()->map(function ($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'phone' => $guest->phone,
                'id_card_number' => $guest->id_card_number,
                'type' => ucwords($guest->type),
                'institution' => $guest->institution,
                'institution_address' => $guest->institution_address,
                'purpose' => $guest->purpose,
                'photo_url' => $guest->photo_url,
                'status' => $guest->status?->name,
                'unit' => $guest->unit?->name,
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
        $pdf->setPaper('a4', 'landscape')->set_option('enable_php', true);
        return $pdf->stream($filename);
    }
}
