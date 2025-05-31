<?php

namespace App\Http\Controllers;

use App\Models\WorkTypes;
use Illuminate\Http\Request;


class DaftarKerjaController extends Controller
{
    public function create()
    {
        return view('WorkTypes.create');
    }
public function store(Request $request)
{
    // Validate the request data
    $request->validate([
        'nama_pekerjaan' => 'required|string|max:255',
        'flatrate' => 'required|integer',
        function ($attribute, $value, $fail) {
            if ($value %15 !== 0) {
                $fail('Flatrate harus kelipatan 15 menit.');
            }
        },
    ]); 

    WorkTypes::create([
        'nama_pekerjaan' => $request->nama_pekerjaan,
        'flatrate' => $request->flatrate,
    ]);
    return redirect()->route('dashboard')->with('success', 'Jenis Pekerjaan Baru Berhasil Ditabahkan');
}
}