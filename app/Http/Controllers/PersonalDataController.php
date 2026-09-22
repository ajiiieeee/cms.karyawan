<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EmployeeController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalDataController extends EmployeeController
{
    public function edit(): View
    {
        return view('personal-data.edit', ['karyawan' => $this->employee()]);
    }
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama_karyawan' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255'], 'telefon' => ['required', 'string', 'max:20'], 'telefon_alternatif' => ['nullable', 'string', 'max:20'], 'alamat' => ['required', 'string', 'max:255'], 'kota' => ['nullable', 'string', 'max:100'], 'provinsi' => ['nullable', 'string', 'max:100'], 'nama_keluarga' => ['required', 'string', 'max:100'], 'hubungan_keluarga' => ['required', 'string', 'max:100'], 'telefon_keluarga' => ['nullable', 'string', 'max:100']]);
        $this->employee()->update($data);
        return back()->with('success', 'Data pribadi berhasil diperbarui.');
    }
}
