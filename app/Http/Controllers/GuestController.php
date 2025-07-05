<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
    public function GetAllGuest()
    {
        $guests = Guest::all()->map(function ($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'institution' => $guest->institution,
                'purpose' => $guest->purpose,
                'photo_url' => $guest->photo_url,
                'created_at' => $guest->created_at,
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
}
