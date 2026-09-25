@extends('layouts.main')

@section('title', 'Personal Data - Detail Profil Karyawan')

@push('styles')
<style>
    /* Pastikan pembungkus konten hero menggunakan align-items center */
.profile-hero-body .d-flex {
    align-items: center !important;
}

/* Jika ingin menaikkan khusus teks nama secara presisi */
.profile-avatar-wrapper + div {
    margin-top: -8px; /* Sesuaikan angka ini jika perlu naik lebih tinggi */
}
    /* Profile Hero Card */
    .profile-hero-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    /* EduDash Modern Cover Banner */
    .profile-cover-banner {
        height: 140px;
        background: linear-gradient(135deg, #25A194 0%, #166534 60%, #0f4c3a 100%);
        position: relative;
        overflow: hidden;
    }
    .profile-cover-banner::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -40px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%);
    }
    .profile-cover-banner::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: 20%;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
    }
    .profile-cover-badge {
        position: absolute;
        top: 16px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Avatar & Profile Hero Body */
    .profile-hero-body {
        padding: 0 28px 24px 28px;
        position: relative;
    }
    .profile-avatar-wrapper {
        margin-top: -60px;
        position: relative;
        display: inline-block;
        z-index: 2;
    }
    .profile-avatar-img {
        width: 112px;
        height: 112px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        object-fit: cover;
        background: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.18);
    }
    .profile-avatar-initials {
        width: 112px;
        height: 112px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        background: linear-gradient(135deg, #25A194 0%, #172554 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.18);
    }
    .online-indicator {
        position: absolute;
        bottom: 6px;
        right: 8px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #10B981;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }

    /* EduDash Metric Pills */
    .hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
    }
    .hero-chip i {
        font-size: 16px;
    }

    /* Section Cards */
    .detail-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 2px 12px -2px rgba(15, 23, 42, 0.04);
        margin-bottom: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .detail-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 24px -6px rgba(15, 23, 42, 0.08);
    }
    .detail-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    /* Colorful Themed Icon Boxes */
    .theme-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 20px;
        transition: transform 0.2s ease;
    }
    .detail-card:hover .theme-icon-box {
        transform: scale(1.05);
    }
    .theme-icon-box.teal { background: #E6F7F5; color: #25A194; }
    .theme-icon-box.blue { background: #EFF6FF; color: #2563EB; }
    .theme-icon-box.purple { background: #F5F3FF; color: #7C3AED; }
    .theme-icon-box.emerald { background: #ECFDF5; color: #059669; }
    .theme-icon-box.amber { background: #FFFBEB; color: #D97706; }
    .theme-icon-box.rose { background: #FFF1F2; color: #E11D48; }
    .theme-icon-box.slate { background: #F1F5F9; color: #475569; }

    /* Modern Item Card Inside Grid */
    .info-tile {
        padding: 14px 16px;
        border-radius: 12px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .info-tile:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
    }
    .info-tile-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .info-tile-icon.teal { background: #E6F7F5; color: #25A194; }
    .info-tile-icon.blue { background: #EFF6FF; color: #2563EB; }
    .info-tile-icon.purple { background: #F5F3FF; color: #7C3AED; }
    .info-tile-icon.emerald { background: #ECFDF5; color: #059669; }
    .info-tile-icon.amber { background: #FFFBEB; color: #D97706; }
    .info-tile-icon.rose { background: #FFF1F2; color: #E11D48; }
    .info-tile-icon.sky { background: #F0F9FF; color: #0284C7; }
    .info-tile-icon.slate { background: #F1F5F9; color: #475569; }

    .info-tile-content {
        min-width: 0;
        flex-grow: 1;
    }
    .info-tile-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #64748B;
        margin-bottom: 2px;
        display: block;
    }
    .info-tile-value {
        font-size: 14px;
        font-weight: 600;
        color: #0F172A;
        line-height: 1.35;
        word-break: break-word;
    }

    /* Status & Pill Badges */
    .status-pulse-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pulse-badge.active {
        background: #DCFCE7;
        color: #15803D;
        border: 1px solid #BBF7D0;
    }
    .status-pulse-badge.inactive {
        background: #FEE2E2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 0 currentColor;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
    }

    /* Action Links */
    .contact-action-btn {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s ease;
    }
    .contact-action-btn.whatsapp {
        background: #DCFCE7;
        color: #15803D;
    }
    .contact-action-btn.whatsapp:hover {
        background: #16A34A;
        color: #ffffff;
    }
    .contact-action-btn.call {
        background: #E0F2FE;
        color: #0369A1;
    }
    .contact-action-btn.call:hover {
        background: #0284C7;
        color: #ffffff;
    }
</style>
@endpush

@php
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $formatTglIndo = function($date) use ($bulanIndo) {
        if (!$date) return '-';
        $carbon = \Carbon\Carbon::parse($date);
        return $carbon->format('d') . ' ' . ($bulanIndo[(int)$carbon->format('m')] ?? $carbon->format('F')) . ' ' . $carbon->format('Y');
    };

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

    // Inisial Nama
    $namaParts = explode(' ', trim($karyawan->nama_karyawan ?? 'Karyawan'));
    $inisial = '';
    if (count($namaParts) >= 2) {
        $inisial = strtoupper(substr($namaParts[0], 0, 1) . substr($namaParts[1], 0, 1));
    } else {
        $inisial = strtoupper(substr($namaParts[0], 0, 2));
    }

    // Hitung Masa Kerja
    $masaKerja = '-';
    if ($karyawan->tanggal_masuk) {
        $diff = \Carbon\Carbon::parse($karyawan->tanggal_masuk)->diff(now());
        $tahun = $diff->y;
        $bulan = $diff->m;
        if ($tahun > 0 && $bulan > 0) {
            $masaKerja = "{$tahun} Thn {$bulan} Bln";
        } elseif ($tahun > 0) {
            $masaKerja = "{$tahun} Tahun";
        } elseif ($bulan > 0) {
            $masaKerja = "{$bulan} Bulan";
        } else {
            $masaKerja = "Baru Bergabung";
        }
    }
@endphp

@section('content')
{{-- Header Halaman --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h4 class="fw-bold mb-0 text-neutral-900">Personal Data</h4>
        <p class="text-neutral-600 mt-1 mb-0 text-sm">Kelola dan tinjau rincian biodata, kepegawaian, dan informasi kontak Anda.</p>
    </div>
    <div>
        <a href="{{ route('personal-data.edit') }}" class="btn btn-primary-600 radius-8 px-20 py-10 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="ri-edit-line" style="font-size: 18px;"></i>
            <span class="fw-semibold">Edit Data Profil</span>
        </a>
    </div>
</div>

{{-- Success Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-24 radius-12 border-0 bg-success-50 text-success-main d-flex align-items-center gap-3 py-14 px-18 shadow-sm" role="alert">
        <div class="w-32-px h-32-px rounded-circle bg-success-main text-white d-flex align-items-center justify-content-center flex-shrink-0">
            <i class="ri-check-line" style="font-size: 20px;"></i>
        </div>
        <div class="flex-grow-1">
            <strong class="d-block text-sm">Berhasil!</strong>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- 1. Hero Profile Card (EduDash Style with Cover Banner) --}}
<div class="profile-hero-card mb-24">
    {{-- Cover Banner --}}
    <div class="profile-cover-banner">
        <div class="profile-cover-badge">
            <i class="ri-verified-badge-line me-1"></i> Data Terverifikasi
        </div>
    </div>

    {{-- Profile Hero Content --}}
    <div class="profile-hero-body">
    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-24">
        {{-- Avatar & Info Utama --}}
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-20">
            <div class="profile-avatar-wrapper flex-shrink-0">
                @if($karyawan->foto)
                    <img 
                        src="{{ asset($karyawan->foto) }}" 
                        alt="{{ $karyawan->nama_karyawan }}" 
                        class="profile-avatar-img"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    >
                    <div class="profile-avatar-initials" style="display: none;">
                        {{ $inisial }}
                    </div>
                @else
                    <div class="profile-avatar-initials">
                        {{ $inisial }}
                    </div>
                @endif

                @if(($karyawan->status_akun ?? 'aktif') === 'aktif')
                    <span class="online-indicator" title="Akun Aktif"></span>
                @endif
            </div>

            {{-- Hapus pt-sm-16 dan sesuaikan margin agar teks naik --}}
            <div class="d-flex flex-column justify-content-center">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-0">
                    <h4 class="fw-bold text-neutral-900 mb-0">{{ $karyawan->nama_karyawan ?? 'Karyawan Demo' }}</h4>
                    @if(($karyawan->status_akun ?? 'aktif') === 'aktif')
                        <span class="status-pulse-badge active">
                            <span class="pulse-dot"></span>
                            <span>Akun Aktif</span>
                        </span>
                    @else
                        <span class="status-pulse-badge inactive">
                            <span class="pulse-dot"></span>
                            <span>Tidak Aktif</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- 2. Grid Informasi Utama (2 Kolom Desktop EduDash) --}}
<div class="row g-24">
    {{-- Kolom Kiri: Data Pribadi & Keluarga --}}
    <div class="col-lg-7">
        {{-- Section: Informasi Pribadi --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-icon-box teal">
                        <i class="ri-user-smile-line"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-neutral-900">Informasi Pribadi</h6>
                        <span class="text-xs text-secondary-light">Biodata identitas resmi karyawan</span>
                    </div>
                </div>
                <span class="badge bg-neutral-100 text-neutral-700 border border-neutral-200 radius-6 px-10 py-4 text-xs fw-semibold">
                    10 Atribut
                </span>
            </div>

            <div class="card-body p-24">
                <div class="row g-16">
                    {{-- Nama Lengkap --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Nama Lengkap</span>
                                <span class="info-tile-value">{{ $karyawan->nama_karyawan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- NIK --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon purple">
                                <i class="ri-fingerprint-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Nomor Induk Kependudukan (NIK)</span>
                                <span class="info-tile-value">{{ $karyawan->nik ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon rose">
                                <i class="ri-genderless-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Jenis Kelamin</span>
                                <span class="info-tile-value">
                                    @if(($karyawan->jenis_kelamin ?? '') === 'laki-laki')
                                        Laki-Laki
                                    @elseif(($karyawan->jenis_kelamin ?? '') === 'perempuan')
                                        Perempuan
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tempat Lahir --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon amber">
                                <i class="ri-map-pin-user-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Tempat Lahir</span>
                                <span class="info-tile-value">{{ $karyawan->tempat_lahir ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon emerald">
                                <i class="ri-calendar-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Tanggal Lahir</span>
                                <span class="info-tile-value">{{ $formatTglIndo($karyawan->tanggal_lahir) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon sky">
                                <i class="ri-mail-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Alamat Email</span>
                                <span class="info-tile-value">{{ $karyawan->email ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Telepon --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon emerald">
                                <i class="ri-phone-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Nomor Telepon</span>
                                <span class="info-tile-value">{{ $karyawan->telefon ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Telepon Alternatif --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon slate">
                                <i class="ri-phone-find-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Telepon Alternatif</span>
                                <span class="info-tile-value">{{ $karyawan->telefon_alternatif ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kota --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon teal">
                                <i class="ri-building-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Kota / Kabupaten</span>
                                <span class="info-tile-value">{{ $karyawan->kota ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Provinsi --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon purple">
                                <i class="ri-map-2-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Provinsi</span>
                                <span class="info-tile-value">{{ $karyawan->provinsi ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Alamat Lengkap (Full Width) --}}
                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon slate">
                                <i class="ri-home-4-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Alamat Lengkap Tempat Tinggal</span>
                                <span class="info-tile-value">{{ $karyawan->alamat ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Informasi Keluarga & Kontak Darurat --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-icon-box rose">
                        <i class="ri-heart-pulse-line"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-neutral-900">Informasi Keluarga & Kontak Darurat</h6>
                        <span class="text-xs text-secondary-light">Kontak prioritas saat kondisi penting</span>
                    </div>
                </div>
                <span class="badge bg-danger-50 text-danger-600 border border-danger-200 radius-6 px-10 py-4 text-xs fw-semibold">
                    Darurat
                </span>
            </div>

            <div class="card-body p-24">
                <div class="row g-16">
                    {{-- Nama Keluarga --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon rose">
                                <i class="ri-user-heart-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Nama Anggota Keluarga</span>
                                <span class="info-tile-value">{{ $karyawan->nama_keluarga ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Hubungan Keluarga --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon amber">
                                <i class="ri-parent-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Hubungan</span>
                                @if($karyawan->hubungan_keluarga)
                                    <span class="badge bg-amber-50 text-amber-700 border border-amber-200 radius-6 px-10 py-4 fw-semibold text-xs mt-1">
                                        {{ $karyawan->hubungan_keluarga }}
                                    </span>
                                @else
                                    <span class="info-tile-value">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Pendidikan Keluarga --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon purple">
                                <i class="ri-graduation-cap-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Pendidikan Keluarga</span>
                                <span class="info-tile-value">{{ $karyawan->pendidikan_keluarga ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Telepon Keluarga with Call Action --}}
                    <div class="col-sm-6">
                        <div class="info-tile">
                            <div class="info-tile-icon emerald">
                                <i class="ri-phone-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Nomor Telepon Darurat</span>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <span class="info-tile-value">{{ $karyawan->telefon_keluarga ?: '-' }}</span>
                                    @if($karyawan->telefon_keluarga)
                                        <a href="tel:{{ $karyawan->telefon_keluarga }}" class="contact-action-btn call" title="Panggil Kontak">
                                            <i class="ri-phone-fill"></i>
                                            <span>Hubungi</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alamat Keluarga --}}
                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon slate">
                                <i class="ri-map-pin-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Alamat Lengkap Keluarga</span>
                                <span class="info-tile-value">{{ $karyawan->alamat_keluarga ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Pekerjaan, Pendidikan & Akun --}}
    <div class="col-lg-5">
        {{-- Section: Informasi Pekerjaan --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-icon-box blue">
                        <i class="ri-briefcase-3-line"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-neutral-900">Informasi Pekerjaan</h6>
                        <span class="text-xs text-secondary-light">Posisi dan status dinas kantor</span>
                    </div>
                </div>
                <span class="badge bg-primary-50 text-primary-600 border border-primary-200 radius-6 px-10 py-4 text-xs fw-semibold">
                    Kepegawaian
                </span>
            </div>

            <div class="card-body p-24">
                <div class="row g-16">
                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon blue">
                                <i class="ri-user-star-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Jabatan / Peran</span>
                                <span class="info-tile-value text-primary-600">{{ $labelJabatan }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon teal">
                                <i class="ri-calendar-event-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Tanggal Masuk Perusahaan</span>
                                <span class="info-tile-value">{{ $formatTglIndo($karyawan->tanggal_masuk) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon emerald">
                                <i class="ri-time-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Lama Masa Kerja</span>
                                <span class="info-tile-value text-success-600">{{ $masaKerja }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Informasi Pendidikan --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-icon-box purple">
                        <i class="ri-graduation-cap-line"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-neutral-900">Informasi Pendidikan</h6>
                        <span class="text-xs text-secondary-light">Kualifikasi studi akademis</span>
                    </div>
                </div>
                <span class="badge bg-purple-50 text-purple-600 border border-purple-200 radius-6 px-10 py-4 text-xs fw-semibold">
                    Akademis
                </span>
            </div>

            <div class="card-body p-24">
                <div class="row g-16">
                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon purple">
                                <i class="ri-award-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Jenjang Terakhir</span>
                                <span class="info-tile-value">{{ $karyawan->pendidikan_terakhir ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon blue">
                                <i class="ri-government-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Lembaga / Universitas</span>
                                <span class="info-tile-value">{{ $karyawan->lembaga_pendidikan ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon emerald">
                                <i class="ri-calendar-check-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Tahun Kelulusan</span>
                                <span class="info-tile-value">{{ $karyawan->tahun_lulus ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Informasi Akun --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-icon-box slate">
                        <i class="ri-shield-keyhole-line"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-neutral-900">Informasi Akun</h6>
                        <span class="text-xs text-secondary-light">Kredensial dan akses login portal</span>
                    </div>
                </div>
                <span class="badge bg-neutral-100 text-neutral-600 border border-neutral-200 radius-6 px-10 py-4 text-xs fw-semibold">
                    Keamanan
                </span>
            </div>

            <div class="card-body p-24">
                <div class="row g-16">
                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon teal">
                                <i class="ri-account-box-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Username Portal</span>
                                <span class="info-tile-value">{{ $karyawan->username ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-tile">
                            <div class="info-tile-icon emerald">
                                <i class="ri-shield-check-line"></i>
                            </div>
                            <div class="info-tile-content">
                                <span class="info-tile-label">Status Akses</span>
                                <div class="mt-1">
                                    @if(($karyawan->status_akun ?? 'aktif') === 'aktif')
                                        <span class="status-pulse-badge active">
                                            <span class="pulse-dot"></span>
                                            <span>Aktif & Terdaftar</span>
                                        </span>
                                    @else
                                        <span class="status-pulse-badge inactive">
                                            <span class="pulse-dot"></span>
                                            <span>Dinonaktifkan</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection