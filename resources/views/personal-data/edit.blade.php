@extends('layouts.main')

@section('title', 'Edit Personal Data - Perbarui Profil')

@push('styles')
<style>
    /* Section Form Card */
    .form-section-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 2px 12px -2px rgba(15, 23, 42, 0.04);
        margin-bottom: 24px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-section-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 20px -4px rgba(15, 23, 42, 0.07);
    }
    .form-section-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    /* Icon Box Header */
    .theme-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 20px;
    }
    .theme-icon-box.teal { background: #E6F7F5; color: #25A194; }
    .theme-icon-box.blue { background: #EFF6FF; color: #2563EB; }
    .theme-icon-box.purple { background: #F5F3FF; color: #7C3AED; }
    .theme-icon-box.emerald { background: #ECFDF5; color: #059669; }
    .theme-icon-box.amber { background: #FFFBEB; color: #D97706; }
    .theme-icon-box.rose { background: #FFF1F2; color: #E11D48; }
    .theme-icon-box.slate { background: #F1F5F9; color: #475569; }

    /* Photo Upload Dropzone */
    .photo-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        padding: 24px;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .photo-upload-zone:hover {
        border-color: #25A194;
        background: #f0fdfa;
    }
    .preview-avatar-circle {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        object-fit: cover;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.16);
    }
    .preview-avatar-initials {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        background: linear-gradient(135deg, #25A194 0%, #172554 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.16);
    }

    /* Input Group Styling */
    .input-group-text {
        border-color: #E2E8F0;
        background-color: #F8FAFC;
        color: #64748B;
        font-size: 18px;
        border-top-left-radius: 8px !important;
        border-bottom-left-radius: 8px !important;
    }
    .form-control, .form-select {
        border-color: #E2E8F0;
        border-radius: 8px;
        font-size: 14px;
        color: #0F172A;
    }
    .form-control:focus, .form-select:focus {
        border-color: #25A194;
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.15);
    }
    .input-group .form-control, .input-group .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    /* Readonly lock field */
    .locked-input {
        background-color: #F1F5F9 !important;
        color: #475569 !important;
        cursor: not-allowed;
        border-color: #E2E8F0;
    }

    /* Floating Action Bar */
    .form-action-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 24px;
        box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 40px;
    }
</style>
@endpush

@php
    $jabatanList = [
        'cso' => 'Customer Service Officer',
        'admin' => 'Staff Administrasi',
        'manager' => 'Manager',
        'programmer' => 'Programmer / Developer',
        'desaingrafis' => 'Desain Grafis',
        'hrd' => 'Human Resource (HRD)',
        'direktur' => 'Direktur',
        'trainer' => 'Trainer / Instruktur',
        'bd' => 'Business Development',
        'bc' => 'Business Consultant',
        'itsupport' => 'IT Support',
        'contentcreator' => 'Content Creator',
    ];

    $labelJabatan = $jabatanList[$karyawan->jabatan ?? ''] ?? ($karyawan->jabatan ? ucwords(str_replace('_', ' ', $karyawan->jabatan)) : 'Staff Karyawan');
    $tanggalMasukFormatted = $karyawan->tanggal_masuk ? \Carbon\Carbon::parse($karyawan->tanggal_masuk)->format('d/m/Y') : '-';

    // Inisial
    $namaParts = explode(' ', trim($karyawan->nama_karyawan ?? 'Karyawan'));
    $inisial = '';
    if (count($namaParts) >= 2) {
        $inisial = strtoupper(substr($namaParts[0], 0, 1) . substr($namaParts[1], 0, 1));
    } else {
        $inisial = strtoupper(substr($namaParts[0], 0, 2));
    }
@endphp

@section('content')
{{-- Header Halaman --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-100 text-primary-600 radius-6 px-8 py-4 text-xs fw-semibold">
                FORMULIR PROFIL
            </span>
            <span class="text-secondary-light text-xs">•</span>
            <span class="text-secondary-light text-xs">Perbarui Biodata Karyawan</span>
        </div>
        <h4 class="fw-bold mb-0 text-neutral-900">Edit Personal Data</h4>
        <p class="text-neutral-600 mt-1 mb-0 text-sm">Ubah data pribadi, riwayat pendidikan, kontak keluarga, dan unggah foto profil terbaru.</p>
    </div>
    <div>
        <a href="{{ route('personal-data.index') }}" class="btn btn-outline-neutral-600 radius-8 px-18 py-10 d-inline-flex align-items-center gap-2">
            <i class="ri-arrow-left-line" style="font-size: 18px;"></i>
            <span>Kembali ke Detail</span>
        </a>
    </div>
</div>

{{-- Global Error Alert --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-24 radius-12 border-0 bg-danger-50 text-danger-main py-14 px-18 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2 mb-8">
            <i class="ri-error-warning-fill" style="font-size: 20px;"></i>
            <strong class="text-sm">Terdapat beberapa data yang perlu diperbaiki:</strong>
        </div>
        <ul class="mb-0 ps-28 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('personal-data.update') }}" enctype="multipart/form-data" id="personalDataForm">
    @csrf
    @method('PUT')

    {{-- Card 5 — Foto Profile (Modern Interactive Upload Zone) --}}
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="d-flex align-items-center gap-3">
                <div class="theme-icon-box teal">
                    <i class="ri-camera-lens-line"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-neutral-900">Foto Profil Karyawan</h6>
                    <span class="text-xs text-secondary-light">Foto resmi identitas portal</span>
                </div>
            </div>
            <span class="badge bg-neutral-100 text-neutral-600 border border-neutral-200 radius-6 px-10 py-4 text-xs fw-semibold">
                Maks. 2 MB
            </span>
        </div>

        <div class="card-body p-24">
            <div class="row align-items-center g-24">
                {{-- Preview Avatar Box --}}
                <div class="col-auto text-center">
                    <div class="position-relative d-inline-block">
                        @if($karyawan->foto)
                            <img 
                                id="avatarPreviewImg"
                                src="{{ asset($karyawan->foto) }}" 
                                alt="Foto Profil" 
                                class="preview-avatar-circle"
                                onerror="this.style.display='none'; document.getElementById('avatarPreviewInitials').style.display='flex';"
                            >
                            <div id="avatarPreviewInitials" class="preview-avatar-initials" style="display: none;">
                                {{ $inisial }}
                            </div>
                        @else
                            <img 
                                id="avatarPreviewImg"
                                src="" 
                                alt="Foto Profil" 
                                class="preview-avatar-circle"
                                style="display: none;"
                            >
                            <div id="avatarPreviewInitials" class="preview-avatar-initials">
                                {{ $inisial }}
                            </div>
                        @endif

                        <label for="fotoInput" class="position-absolute bottom-0 end-0 bg-primary-600 text-white rounded-circle p-8 d-flex align-items-center justify-content-center cursor-pointer shadow-sm border border-2 border-white" style="width: 34px; height: 34px;" title="Ganti Foto">
                            <i class="ri-camera-line" style="font-size: 16px;"></i>
                        </label>
                    </div>
                </div>

                {{-- Upload Dropzone & Details --}}
                <div class="col">
                    <label for="fotoInput" class="photo-upload-zone w-100 mb-0">
                        <div class="w-48-px h-48-px rounded-circle bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center mb-12">
                            <i class="ri-upload-cloud-2-line" style="font-size: 24px;"></i>
                        </div>
                        <h6 class="text-sm fw-semibold text-neutral-900 mb-4">
                            Klik di sini untuk memilih foto profil baru
                        </h6>
                        <p class="text-xs text-secondary-light mb-8">
                            Dukungan format file: <strong>JPG, JPEG, PNG</strong> (Ukuran maksimal <strong>2MB</strong>)
                        </p>
                        <span class="btn btn-sm btn-outline-primary radius-8 px-14 py-6 d-inline-flex align-items-center gap-1">
                            <i class="ri-folder-upload-line"></i>
                            <span>Jelajahi File</span>
                        </span>
                    </label>

                    <input 
                        type="file" 
                        id="fotoInput" 
                        name="foto" 
                        class="d-none" 
                        accept="image/jpeg,image/png,image/jpg"
                    >

                    <div id="uploadFeedback" class="mt-8"></div>

                    @error('foto')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Card 1 — Data Pribadi --}}
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="d-flex align-items-center gap-3">
                <div class="theme-icon-box blue">
                    <i class="ri-user-smile-line"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-neutral-900">Data Pribadi</h6>
                    <span class="text-xs text-secondary-light">Lengkapi informasi biodata resmi Anda</span>
                </div>
            </div>
            <span class="badge bg-primary-50 text-primary-600 border border-primary-200 radius-6 px-10 py-4 text-xs fw-semibold">
                <span class="text-danger-600 me-1">*</span>Wajib Diisi
            </span>
        </div>

        <div class="card-body p-24">
            <div class="row g-20">
                {{-- Nama Lengkap --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Nama Lengkap <span class="text-danger-600">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-user-3-line"></i></span>
                        <input 
                            type="text" 
                            name="nama_karyawan" 
                            class="form-control @error('nama_karyawan') is-invalid @enderror" 
                            value="{{ old('nama_karyawan', $karyawan->nama_karyawan) }}" 
                            placeholder="Contoh: Mochammad Adji"
                            required
                        >
                    </div>
                    @error('nama_karyawan')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- NIK --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Nomor Induk Kependudukan (NIK)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-fingerprint-line"></i></span>
                        <input 
                            type="text" 
                            name="nik" 
                            class="form-control @error('nik') is-invalid @enderror" 
                            value="{{ old('nik', $karyawan->nik) }}" 
                            placeholder="Contoh: 3509xxxxxxxxxxxx"
                            maxlength="18"
                        >
                    </div>
                    @error('nik')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Jenis Kelamin
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-genderless-line"></i></span>
                        <select 
                            name="jenis_kelamin" 
                            class="form-select @error('jenis_kelamin') is-invalid @enderror"
                        >
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="laki-laki" @selected(old('jenis_kelamin', $karyawan->jenis_kelamin) === 'laki-laki')>
                                Laki-Laki
                            </option>
                            <option value="perempuan" @selected(old('jenis_kelamin', $karyawan->jenis_kelamin) === 'perempuan')>
                                Perempuan
                            </option>
                        </select>
                    </div>
                    @error('jenis_kelamin')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tempat Lahir --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Tempat Lahir
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-map-pin-user-line"></i></span>
                        <input 
                            type="text" 
                            name="tempat_lahir" 
                            class="form-control @error('tempat_lahir') is-invalid @enderror" 
                            value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}" 
                            placeholder="Contoh: Surabaya"
                        >
                    </div>
                    @error('tempat_lahir')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tanggal Lahir --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Tanggal Lahir
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                        <input 
                            type="date" 
                            name="tanggal_lahir" 
                            class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                            value="{{ old('tanggal_lahir', optional($karyawan->tanggal_lahir)->format('Y-m-d')) }}"
                        >
                    </div>
                    @error('tanggal_lahir')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Alamat Email <span class="text-danger-600">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $karyawan->email) }}" 
                            placeholder="nama@domain.com"
                            required
                        >
                    </div>
                    @error('email')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Nomor Telepon --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Nomor Telepon / WhatsApp <span class="text-danger-600">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-phone-line"></i></span>
                        <input 
                            type="text" 
                            name="telefon" 
                            class="form-control @error('telefon') is-invalid @enderror" 
                            value="{{ old('telefon', $karyawan->telefon) }}" 
                            placeholder="Contoh: 081234567890"
                            required
                        >
                    </div>
                    @error('telefon')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Telepon Alternatif --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Telepon Alternatif (Opsional)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-phone-find-line"></i></span>
                        <input 
                            type="text" 
                            name="telefon_alternatif" 
                            class="form-control @error('telefon_alternatif') is-invalid @enderror" 
                            value="{{ old('telefon_alternatif', $karyawan->telefon_alternatif) }}" 
                            placeholder="Nomor kontak darurat kedua"
                        >
                    </div>
                    @error('telefon_alternatif')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Kota --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Kota / Kabupaten
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-building-line"></i></span>
                        <input 
                            type="text" 
                            name="kota" 
                            class="form-control @error('kota') is-invalid @enderror" 
                            value="{{ old('kota', $karyawan->kota) }}" 
                            placeholder="Contoh: Surabaya"
                        >
                    </div>
                    @error('kota')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Provinsi --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Provinsi
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-map-2-line"></i></span>
                        <input 
                            type="text" 
                            name="provinsi" 
                            class="form-control @error('provinsi') is-invalid @enderror" 
                            value="{{ old('provinsi', $karyawan->provinsi) }}" 
                            placeholder="Contoh: Jawa Timur"
                        >
                    </div>
                    @error('provinsi')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Alamat Lengkap (Full Width) --}}
                <div class="col-12">
                    <label class="form-label">
                        Alamat Lengkap Tempat Tinggal <span class="text-danger-600">*</span>
                    </label>
                    <textarea 
                        name="alamat" 
                        rows="3" 
                        class="form-control @error('alamat') is-invalid @enderror" 
                        placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan"
                        required
                    >{{ old('alamat', $karyawan->alamat) }}</textarea>
                    @error('alamat')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2 — Pendidikan --}}
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="d-flex align-items-center gap-3">
                <div class="theme-icon-box purple">
                    <i class="ri-graduation-cap-line"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-neutral-900">Riwayat Pendidikan Terakhir</h6>
                    <span class="text-xs text-secondary-light">Kualifikasi akademik formal</span>
                </div>
            </div>
            <span class="badge bg-purple-50 text-purple-600 border border-purple-200 radius-6 px-10 py-4 text-xs fw-semibold">
                Akademik
            </span>
        </div>

        <div class="card-body p-24">
            <div class="row g-20">
                <div class="col-md-4">
                    <label class="form-label">
                        Jenjang Pendidikan Terakhir
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-award-line"></i></span>
                        <input 
                            type="text" 
                            name="pendidikan_terakhir" 
                            class="form-control @error('pendidikan_terakhir') is-invalid @enderror" 
                            value="{{ old('pendidikan_terakhir', $karyawan->pendidikan_terakhir) }}" 
                            placeholder="Contoh: S1 / D4 / D3 / SMA"
                        >
                    </div>
                    @error('pendidikan_terakhir')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Nama Lembaga / Universitas
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-government-line"></i></span>
                        <input 
                            type="text" 
                            name="lembaga_pendidikan" 
                            class="form-control @error('lembaga_pendidikan') is-invalid @enderror" 
                            value="{{ old('lembaga_pendidikan', $karyawan->lembaga_pendidikan) }}" 
                            placeholder="Contoh: Politeknik Negeri Jember"
                        >
                    </div>
                    @error('lembaga_pendidikan')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Tahun Kelulusan
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-calendar-check-line"></i></span>
                        <input 
                            type="number" 
                            name="tahun_lulus" 
                            min="1950" 
                            max="{{ date('Y') + 5 }}" 
                            class="form-control @error('tahun_lulus') is-invalid @enderror" 
                            value="{{ old('tahun_lulus', $karyawan->tahun_lulus) }}" 
                            placeholder="Contoh: 2024"
                        >
                    </div>
                    @error('tahun_lulus')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3 — Pekerjaan (Resmi / Dikelola HRD) --}}
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="d-flex align-items-center gap-3">
                <div class="theme-icon-box blue">
                    <i class="ri-briefcase-3-line"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-neutral-900">Informasi Pekerjaan</h6>
                    <span class="text-xs text-secondary-light">Data kepegawaian resmi kantor</span>
                </div>
            </div>
            <span class="badge bg-neutral-100 text-secondary-light border border-neutral-200 radius-6 px-10 py-4 text-xs fw-semibold">
                <i class="ri-lock-line me-1"></i>Terkunci (Dikelola HRD)
            </span>
        </div>

        <div class="card-body p-24">
            <div class="row g-20">
                <div class="col-md-6">
                    <label class="form-label">
                        Jabatan / Posisi
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-user-star-line"></i></span>
                        <input 
                            type="text" 
                            class="form-control locked-input" 
                            value="{{ $labelJabatan }}" 
                            readonly 
                            disabled
                        >
                    </div>
                    <span class="text-xs text-secondary-light mt-4 d-block">
                        Hubungi tim HRD jika terjadi mutasi atau perubahan jenjang jabatan.
                    </span>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Tanggal Masuk Kerja
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                        <input 
                            type="text" 
                            class="form-control locked-input" 
                            value="{{ $tanggalMasukFormatted }}" 
                            readonly 
                            disabled
                        >
                    </div>
                    <span class="text-xs text-secondary-light mt-4 d-block">
                        Tanggal resmi awal masa pengabdian kerja.
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4 — Keluarga & Kontak Darurat --}}
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="d-flex align-items-center gap-3">
                <div class="theme-icon-box rose">
                    <i class="ri-heart-pulse-line"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-neutral-900">Data Keluarga & Kontak Darurat</h6>
                    <span class="text-xs text-secondary-light">Pihak yang dapat dihubungi saat situasi darurat</span>
                </div>
            </div>
            <span class="badge bg-danger-50 text-danger-600 border border-danger-200 radius-6 px-10 py-4 text-xs fw-semibold">
                Kontak Darurat
            </span>
        </div>

        <div class="card-body p-24">
            <div class="row g-20">
                <div class="col-md-6">
                    <label class="form-label">
                        Nama Lengkap Keluarga <span class="text-danger-600">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-user-heart-line"></i></span>
                        <input 
                            type="text" 
                            name="nama_keluarga" 
                            class="form-control @error('nama_keluarga') is-invalid @enderror" 
                            value="{{ old('nama_keluarga', $karyawan->nama_keluarga) }}" 
                            placeholder="Contoh: Budi Santoso"
                            required
                        >
                    </div>
                    @error('nama_keluarga')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Hubungan Keluarga <span class="text-danger-600">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-parent-line"></i></span>
                        <input 
                            type="text" 
                            name="hubungan_keluarga" 
                            class="form-control @error('hubungan_keluarga') is-invalid @enderror" 
                            value="{{ old('hubungan_keluarga', $karyawan->hubungan_keluarga) }}" 
                            placeholder="Contoh: Ayah / Ibu / Pasangan / Saudara"
                            required
                        >
                    </div>
                    @error('hubungan_keluarga')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Pendidikan Keluarga
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-graduation-cap-line"></i></span>
                        <input 
                            type="text" 
                            name="pendidikan_keluarga" 
                            class="form-control @error('pendidikan_keluarga') is-invalid @enderror" 
                            value="{{ old('pendidikan_keluarga', $karyawan->pendidikan_keluarga) }}" 
                            placeholder="Contoh: S1 / SMA"
                        >
                    </div>
                    @error('pendidikan_keluarga')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Nomor Telepon Keluarga / Darurat
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-phone-line"></i></span>
                        <input 
                            type="text" 
                            name="telefon_keluarga" 
                            class="form-control @error('telefon_keluarga') is-invalid @enderror" 
                            value="{{ old('telefon_keluarga', $karyawan->telefon_keluarga) }}" 
                            placeholder="Contoh: 081234567890"
                        >
                    </div>
                    @error('telefon_keluarga')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Alamat Tempat Tinggal Keluarga
                    </label>
                    <textarea 
                        name="alamat_keluarga" 
                        rows="3" 
                        class="form-control @error('alamat_keluarga') is-invalid @enderror" 
                        placeholder="Alamat lengkap domisili kontak darurat"
                    >{{ old('alamat_keluarga', $karyawan->alamat_keluarga) }}</textarea>
                    @error('alamat_keluarga')
                        <span class="text-danger-600 text-xs mt-4 d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Card 6 — Akun Pengguna --}}
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="d-flex align-items-center gap-3">
                <div class="theme-icon-box slate">
                    <i class="ri-shield-keyhole-line"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-neutral-900">Akun Pengguna Sistem</h6>
                    <span class="text-xs text-secondary-light">Kredensial login portal</span>
                </div>
            </div>
            <span class="badge bg-neutral-100 text-secondary-light border border-neutral-200 radius-6 px-10 py-4 text-xs fw-semibold">
                <i class="ri-lock-line me-1"></i>Hak Akses
            </span>
        </div>

        <div class="card-body p-24">
            <div class="row g-20">
                <div class="col-md-6">
                    <label class="form-label">
                        Username Login
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-account-box-line"></i></span>
                        <input 
                            type="text" 
                            class="form-control locked-input" 
                            value="{{ $karyawan->username ?? '-' }}" 
                            readonly 
                            disabled
                        >
                    </div>
                    <span class="text-xs text-secondary-light mt-4 d-block">
                        Username login terikat dengan akun portal Anda.
                    </span>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Status Akun
                    </label>
                    <div class="pt-6">
                        @if(($karyawan->status_akun ?? 'aktif') === 'aktif')
                            <span class="badge bg-success-100 text-success-700 border border-success-200 radius-8 px-14 py-8 d-inline-flex align-items-center gap-2">
                                <i class="ri-checkbox-circle-fill text-success-main" style="font-size: 16px;"></i>
                                <span class="fw-semibold text-sm">Akun Aktif & Terverifikasi</span>
                            </span>
                        @else
                            <span class="badge bg-danger-100 text-danger-700 border border-danger-200 radius-8 px-14 py-8 d-inline-flex align-items-center gap-2">
                                <i class="ri-close-circle-fill text-danger-main" style="font-size: 16px;"></i>
                                <span class="fw-semibold text-sm">Akun Dinonaktifkan</span>
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-secondary-light mt-8 d-block">
                        Status akun diatur oleh Super Admin sistem.
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Floating Action Bar --}}
    <div class="form-action-bar">
        <div class="text-xs text-secondary-light d-none d-sm-block">
            <i class="ri-information-line me-1 text-primary-600"></i> Pastikan data yang dimasukkan sudah benar sebelum disimpan.
        </div>

        <div class="d-flex align-items-center gap-12 ms-auto">
            <a 
                href="{{ route('personal-data.index') }}" 
                class="btn btn-outline-neutral-600 radius-8 px-24 py-11 d-inline-flex align-items-center gap-2"
            >
                <i class="ri-close-line" style="font-size: 18px;"></i>
                <span>Batal</span>
            </a>

            <button 
                type="submit" 
                class="btn btn-primary-600 radius-8 px-28 py-11 d-inline-flex align-items-center gap-2 shadow-sm"
            >
                <i class="ri-save-line" style="font-size: 18px;"></i>
                <span class="fw-semibold">Simpan Perubahan</span>
            </button>
        </div>
    </div>
</form>

{{-- Live Photo Preview JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fotoInput = document.getElementById('fotoInput');
        const avatarPreviewImg = document.getElementById('avatarPreviewImg');
        const avatarPreviewInitials = document.getElementById('avatarPreviewInitials');
        const uploadFeedback = document.getElementById('uploadFeedback');
        const originalImgSrc = avatarPreviewImg ? avatarPreviewImg.src : '';
        const hadOriginalImg = avatarPreviewImg && avatarPreviewImg.style.display !== 'none' && originalImgSrc !== '';

        if (fotoInput) {
            fotoInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) {
                    return;
                }

                // Validasi ukuran file (2MB)
                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    uploadFeedback.innerHTML = `
                        <div class="alert alert-danger py-8 px-12 radius-8 text-xs d-flex align-items-center justify-content-between mb-0">
                            <span><i class="ri-error-warning-line me-1"></i> Ukuran foto melebihi 2MB (${(file.size / (1024 * 1024)).toFixed(2)} MB). Silakan pilih file lebih kecil.</span>
                        </div>
                    `;
                    fotoInput.value = '';
                    resetPreview();
                    return;
                }

                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    uploadFeedback.innerHTML = `
                        <div class="alert alert-danger py-8 px-12 radius-8 text-xs d-flex align-items-center justify-content-between mb-0">
                            <span><i class="ri-error-warning-line me-1"></i> Format file tidak didukung! Gunakan format JPG, JPEG, atau PNG.</span>
                        </div>
                    `;
                    fotoInput.value = '';
                    resetPreview();
                    return;
                }

                // Tampilkan Live Preview
                const reader = new FileReader();
                reader.onload = function (event) {
                    if (avatarPreviewImg) {
                        avatarPreviewImg.src = event.target.result;
                        avatarPreviewImg.style.display = 'block';
                    }
                    if (avatarPreviewInitials) {
                        avatarPreviewInitials.style.display = 'none';
                    }

                    uploadFeedback.innerHTML = `
                        <div class="alert alert-success py-8 px-12 radius-8 text-xs d-flex align-items-center justify-content-between mb-0 bg-success-50 text-success-main border-0">
                            <span class="d-flex align-items-center gap-1">
                                <i class="ri-checkbox-circle-fill text-sm"></i>
                                <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB) siap diunggah.
                            </span>
                            <button type="button" class="btn btn-link text-danger-600 p-0 text-xs text-decoration-none fw-semibold" id="btnCancelPhoto">
                                <i class="ri-delete-bin-line me-1"></i>Hapus
                            </button>
                        </div>
                    `;

                    document.getElementById('btnCancelPhoto').addEventListener('click', function () {
                        fotoInput.value = '';
                        resetPreview();
                        uploadFeedback.innerHTML = '';
                    });
                };
                reader.readAsDataURL(file);
            });
        }

        function resetPreview() {
            if (hadOriginalImg) {
                avatarPreviewImg.src = originalImgSrc;
                avatarPreviewImg.style.display = 'block';
                if (avatarPreviewInitials) avatarPreviewInitials.style.display = 'none';
            } else {
                if (avatarPreviewImg) avatarPreviewImg.style.display = 'none';
                if (avatarPreviewInitials) avatarPreviewInitials.style.display = 'flex';
            }
        }
    });
</script>
@endsection
