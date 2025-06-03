<?php

namespace App\Http\Controllers;

use App\Models\WorkTypes;
use Illuminate\Http\Request;

class DaftarKerjaController extends Controller
{
    public function create()
    {
        $workTypes = WorkTypes::all();
        return view('WorkTypes.create', compact('workTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pekerjaan' => 'required|string|max:255',
            'flatrate' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $allowed = [15, 25, 30, 40, 45, 60, 90, 120]; // tambahkan sesuai kebutuhan
                    if (!in_array($value, $allowed)) {
                        $fail('Estimasi hanya boleh 15, 25, 30, 40, 45, 60, 90, atau 120 menit.');
                    }
                },
            ],
        ]);

        WorkTypes::create([
            'nama_pekerjaan' => $request->nama_pekerjaan,
            'flatrate' => $request->flatrate,
        ]);
        return redirect()->route('WorkTypes.create')->with('success', 'Jenis Pekerjaan Baru Berhasil Ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pekerjaan' => 'required|string|max:255',
            'flatrate' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $allowed = [15, 25, 30, 40, 45, 60, 90, 120];
                    if (!in_array($value, $allowed)) {
                        $fail('Estimasi hanya boleh 15, 25, 30, 40, 45, 60, 90, atau 120 menit.');
                    }
                },
            ],
        ]);

        $workType = WorkTypes::findOrFail($id);
        $workType->update([
            'nama_pekerjaan' => $request->nama_pekerjaan,
            'flatrate' => $request->flatrate,
        ]);
        return redirect()->route('WorkTypes.create')->with('success', 'Jenis Pekerjaan berhasil diupdate');
    }

    public function destroy($id)
    {
        $workType = WorkTypes::findOrFail($id);
        $workType->delete();
        return redirect()->route('WorkTypes.create')->with('success', 'Jenis Pekerjaan berhasil dihapus');
    }
}