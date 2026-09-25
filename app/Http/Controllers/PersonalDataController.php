<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EmployeeController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalDataController extends EmployeeController
{
    public function index(): View
    {
        return view('personal-data.index', ['karyawan' => $this->employee()]);
    }

    public function edit(): View
    {
        return view('personal-data.edit', ['karyawan' => $this->employee()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $employee = $this->employee();

        $data = $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'max:18'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'email' => ['required', 'email', 'max:255'],
            'telefon' => ['required', 'string', 'max:20'],
            'telefon_alternatif' => ['nullable', 'string', 'max:20'],
            'alamat' => ['required', 'string', 'max:255'],
            'kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'lembaga_pendidikan' => ['nullable', 'string', 'max:100'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1950', 'max:' . (date('Y') + 10)],
            'nama_keluarga' => ['required', 'string', 'max:100'],
            'alamat_keluarga' => ['nullable', 'string', 'max:255'],
            'pendidikan_keluarga' => ['nullable', 'string', 'max:100'],
            'hubungan_keluarga' => ['required', 'string', 'max:100'],
            'telefon_keluarga' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'profile_' . $employee->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/profile');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $data['foto'] = 'uploads/profile/' . $filename;
        }

        $employee->update($data);

        return redirect()->route('personal-data.index')->with('success', 'Data pribadi berhasil diperbarui.');
    }
}
