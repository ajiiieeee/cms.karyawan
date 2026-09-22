@extends('layouts.main')

@section('title', 'Form Izin Cuti')

@section('content')
<div class="d-flex justify-content-between mb-24">
    <h5>Form Izin Cuti</h5>
    <a
        class="btn btn-primary-600"
        href="{{ route('leave-requests.create') }}"
    >
        Ajukan cuti
    </a>
</div>

@include('partials.alert')

<div class="card">
    <div class="card-body table-responsive">
        <table class="table">
            <thead><tr><th>Jenis</th><th>Periode</th><th>Hari</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td>{{ $item->kategoriCuti->nama_kategori }}</td>
                        <td>{{ $item->tanggal_awal->format('d M Y') }} - {{ $item->tanggal_akhir->format('d M Y') }}</td>
                        <td>{{ $item->jumlah_hari }}</td>
                        <td>
                            {{ $item->status_pengajuan === 'Pending' ? 'Menunggu' : ($item->status_pengajuan === 'Approve' ? 'Disetujui' : 'Ditolak') }}
                        </td>
                        <td>
                            @if($item->status_pengajuan === 'Pending')
                                <a href="{{ route('leave-requests.edit', $item) }}">Ubah</a>
                                <form class="d-inline" method="POST" action="{{ route('leave-requests.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link text-danger">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
