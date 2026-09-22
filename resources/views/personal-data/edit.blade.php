@extends('layouts.main')

@section('title', 'Data Pribadi')

@section('content')
<div class="breadcrumb mb-24">
    <h6 class="fw-semibold mb-0">Data Pribadi</h6>
    <p class="text-neutral-600 mt-4 mb-0">Perbarui data pribadi dan kontak darurat Anda.</p>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Data Pribadi</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('personal-data.update') }}">
            @csrf
            @method('PUT')
            @include('partials.alert')
            @php
                $fields = [
                    'nama_karyawan' => 'Nama lengkap',
                    'email' => 'Email',
                    'telefon' => 'Nomor telepon',
                    'telefon_alternatif' => 'Telepon alternatif',
                    'alamat' => 'Alamat',
                    'kota' => 'Kota',
                    'provinsi' => 'Provinsi',
                    'nama_keluarga' => 'Kontak darurat',
                    'hubungan_keluarga' => 'Hubungan',
                    'telefon_keluarga' => 'Telepon kontak darurat',
                ];
                $requiredFields = ['nama_karyawan', 'email', 'telefon', 'alamat', 'nama_keluarga', 'hubungan_keluarga'];
            @endphp
            <div class="row gy-3">
                @foreach($fields as $field => $label)
                    <div class="col-md-6">
                        <label class="form-label">{{ $label }}</label>
                        <input class="form-control" name="{{ $field }}" value="{{ old($field, $karyawan->$field) }}" {{ in_array($field, $requiredFields) ? 'required' : '' }}>
                    </div>
                @endforeach
            </div>
            <button class="btn btn-primary-600 mt-24">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
