@extends('layouts.main')

@section('title', 'Detail Penjadwalan Kursus')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Detail Penjadwalan Kursus</h6>
        <p class="text-neutral-600 mt-4 mb-0">Penjadwalan &raquo; Detail</p>
      </div>
    </div>

    <!-- Info Siswa & Trainer -->
    <div class="row g-24 mb-24">
      <div class="col-lg-6">
        <div class="card shadow-1 radius-8 h-100">
          <div class="card-header bg-neutral-50 py-12 px-24">
            <h6 class="fw-semibold mb-0 text-sm text-neutral-600 d-flex align-items-center gap-6">
              <i class="ri-user-3-line"></i> Informasi Siswa
            </h6>
          </div>
          <div class="card-body p-24">
            <table class="table table-borderless table-sm mb-0">
              <tr>
                <td class="text-neutral-500 text-md fw-medium" width="130">Nama Siswa</td>
                <td class="text-md fw-semibold"> : {{ $penjadwalan->siswa->nama_siswa ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">NIK</td>
                <td class="text-md"> : {{ $penjadwalan->siswa->nik ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">No Telepon</td>
                <td class="text-md"> : {{ $penjadwalan->siswa->no_telepon ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">Email</td>
                <td class="text-md"> : {{ $penjadwalan->siswa->email ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">Alamat</td>
                <td class="text-md"> : {{ $penjadwalan->siswa->alamat ?? '-' }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card shadow-1 radius-8 h-100">
          <div class="card-header bg-neutral-50 py-12 px-24">
            <h6 class="fw-semibold mb-0 text-sm text-neutral-600 d-flex align-items-center gap-6">
              <i class="ri-user-star-line"></i> Informasi Trainer
            </h6>
          </div>
          <div class="card-body p-24">
            <table class="table table-borderless table-sm mb-0">
              <tr>
                <td class="text-neutral-500 text-md fw-medium" width="130">Nama Trainer</td>
                <td class="text-md fw-semibold"> : {{ $penjadwalan->karyawan->nama_karyawan ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">NIK</td>
                <td class="text-md"> : {{ $penjadwalan->karyawan->nik ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">Telepon</td>
                <td class="text-md"> : {{ $penjadwalan->karyawan->telefon ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">Email</td>
                <td class="text-md"> : {{ $penjadwalan->karyawan->email ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-neutral-500 text-md fw-medium">Jabatan</td>
                <td class="text-md"> : {{ ucfirst($penjadwalan->karyawan->jabatan ?? '-') }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Jadwal -->
    <div class="card shadow-1 radius-8 mb-24">
      <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
        <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
          <i class="ri-calendar-schedule-line text-lg"></i> Detail Penjadwalan
        </h6>
      </div>
      <div class="card-body p-24">
        <div class="row gy-3">
            <div class="col-lg-6">
              <table class="table table-borderless table-sm mb-0">
                  <tr>
                      <td class="text-neutral-500 text-md fw-medium" width="140">Lokasi</td>
                      <td class="text-neutral-500 text-md fw-medium"> : {{ $penjadwalan->lokasi }}</td>
                  </tr>
                  <tr>
                      <td class="text-neutral-500 text-md fw-medium">Jumlah Pertemuan</td>
                      <td class="text-neutral-500 text-md"> : {{ $penjadwalan->jumlah_pertemuan ?? '-' }}</td>
                  </tr>
                  <tr>
                      <td class="text-neutral-500 text-md fw-medium">Bidang Studi</td>
                      <td class="text-neutral-500 text-md"> : {{ $penjadwalan->bidangStudi->nama_bidang_studi ?? '-' }}</td>
                  </tr>
                  <tr>
                      <td class="text-neutral-500 text-md fw-medium">Level Kelas</td>
                      <td class="text-neutral-500 text-md"> : {{ $penjadwalan->levelKelas->nama_level ?? '-' }}</td>
                  </tr>
              </table>
            </div>
            @php
                $statusJadwal = match($penjadwalan->status_jadwal) {
                  0 => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-info">Berjalan</span>',
                  1 => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-success">Selesai</span>',
                  2 => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-warning">DO</span>',
              };
            @endphp
            <div class="col-lg-6">
              <table class="table table-borderless table-sm mb-0">
                <tr>
                    <td class="text-neutral-500 text-md fw-medium" width="140">Tanggal Mulai</td>
                    <td class="text-neutral-500 text-md"> : {{ $penjadwalan->tgl_mulai->format('d/m/Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-neutral-500 text-md fw-medium">Tanggal Selesai</td>
                    <td class="text-neutral-500 text-md"> : {{ $penjadwalan->tgl_selesai ? $penjadwalan->tgl_selesai->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-neutral-500 text-md fw-medium">Status Jadwal</td>
                    <td class="text-neutral-500 text-md fw-medium"> : {!! $statusJadwal !!}</td>
                </tr>
                @if($penjadwalan->status_jadwal == 2 && isset($penjadwalan->keterangan))
                  <tr>
                      <td class="text-neutral-500 text-md fw-medium">Keterangan</td>
                      <td class="text-neutral-500 text-md fw-medium"> : {{ $penjadwalan->keterangan ?? '-' }}</td>
                  </tr>
                @endif
              </table>
            </div>
        </div>
      </div>
    </div>

    <!-- Jadwal Kursus -->
    <div class="card shadow-1 radius-8 mb-24">
      <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
        <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
          <i class="ri-time-line text-lg"></i> Jadwal Kursus
        </h6>
      </div>
      <div class="card-body p-24">
        @if($penjadwalan->detailPenjadwalan->count() > 0)
          <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
              <thead class="bg-neutral-100">
                <tr>
                  <th class="text-center text-sm fw-semibold" style="width: 60px;">No</th>
                  <th class="text-sm fw-semibold">Hari</th>
                  <th class="text-sm fw-semibold" width="100">Jam Mulai</th>
                </tr>
              </thead>
              <tbody>
                @foreach($penjadwalan->detailPenjadwalan as $index => $jadwal)
                  <tr>
                    <td class="text-center text-sm">{{ $index + 1 }}</td>
                    <td class="text-sm">{{ $jadwal->hari }}</td>
                    <td class="text-sm">{{ $jadwal->jam_mulai }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-24 text-muted">
            <i class="ri-calendar-todo-line text-3xl d-block mb-8"></i>
            <p class="mb-0">Belum ada jadwal kursus yang ditentukan.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Dokumen GBMP -->
    <div class="card shadow-1 radius-8 mb-24">
      <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
        <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
          <i class="ri-file-text-line text-lg"></i> Dokumen GBMP
        </h6>
      </div>
      <div class="card-body p-24">
        @if($penjadwalan->nama_gbmp && $penjadwalan->upload_gbmp)
          <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
              <thead class="bg-neutral-100">
                <tr>
                  <th class="text-center text-sm fw-semibold" width="60">No</th>
                  <th class="text-sm fw-semibold">Nama GBMP</th>
                  <th class="text-sm fw-semibold">File</th>
                  <th class="text-center text-sm fw-semibold" width="100">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center text-sm">1</td>
                  <td class="text-sm">{{ $penjadwalan->nama_gbmp }}</td>
                  <td class="text-sm"><i class="ri-file-text-line me-4"></i>{{ basename($penjadwalan->upload_gbmp) }}</td>
                  <td class="text-center">
                    <a href="{{ asset('storage/' . $penjadwalan->upload_gbmp) }}" target="_blank" class="btn btn-sm btn-outline-info-600" title="Preview">
                      <i class="ri-eye-line"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-24 text-muted">
            <i class="ri-file-text-line text-3xl d-block mb-8"></i>
            <p class="mb-0">Belum ada dokumen GBMP yang diunggah.</p>
          </div>
        @endif
      </div>
    </div>

    <div class="d-flex justify-content-end gap-8">
      <a href="{{ route('penjadwalan.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
      @if(Auth::user()->hasMenuAccess('penjadwalan', 'edit'))
      <a href="{{ route('penjadwalan.edit', \App\Helpers\IdEncryptor::encrypt($penjadwalan->id)) }}" class="btn btn-primary-600">
        <i class="ri-edit-line"></i> Edit Penjadwalan
      </a>
      @endif
    </div>
@endsection
