<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // GET ALL
    public function index(Request $request)
    {
        $perPage = $request->get('limit', 10);
    
        $query = Unit::query();
    
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%$search%")
                  ->orWhere('unit_phone', 'ilike', "%$search%");
            });
        }
    
        $units = $query->orderBy('name')->paginate($perPage);
    
        return response()->json([
            'data' => $units->items(),
            'total' => $units->total(),
            'per_page' => $units->perPage(),
            'current_page' => $units->currentPage(),
        ]);
    }
    
    

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_phone' => 'required|string|max:20',
        ]);

        $unit = Unit::create([
            'name' => $request->name,
            'unit_phone' => $request->unit_phone,
        ]);

        return resJSON(1, "Unit berhasil ditambahkan", $unit, 201);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_phone' => 'required|string|max:20',
        ]);
        $unit = Unit::findOrFail($id);
        $unit->update([
            'name' => $request->name,
            'unit_phone' => $request->unit_phone,
        ]);

        return resJSON(1, "Unit berhasil diperbarui", $unit, 200);
    }

    // DELETE
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
    
        return resJSON(1, "Unit berhasil dihapus", null, 200);
    }
    
}
