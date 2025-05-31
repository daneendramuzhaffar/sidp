<?php

namespace App\Http\Controllers;

use App\Models\Workers;
use Illuminate\Http\Request;


class workerController extends Controller
{
    public function create()
    {
        return view('workers.create');
    }
public function store(Request $request)
{
    // Validate the request data
    $request->validate([
        'nama' => 'required|string|max:255',
        'status' => 'required|string|max:50',
        'mulai' => 'required'
    ]);

    $mulai = $request->input('mulai');
    $selesai = date('H:i:s', strtotime($mulai) + 8 * 3600); 

    Workers::create([
        'nama' => $request->nama,
        'status' => $request->status,
        'mulai' => $mulai,
        'selesai' => $selesai,
    ]);
    return redirect()->route('dashboard')->with('success', 'Teknisi Berhasil Ditambahkan');
}
}