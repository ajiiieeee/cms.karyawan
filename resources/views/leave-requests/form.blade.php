@extends('layouts.main')

@section('title', 'Form Izin Cuti')

@section('content')
<div class="card">
    <div class="card-header"><h5>{{ $requestItem->exists ? 'Ubah' : 'Ajukan' }} Cuti</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ $requestItem->exists ? route('leave-requests.update', $requestItem) : route('leave-requests.store') }}">
            @csrf
            @if($requestItem->exists) @method('PUT') @endif
            @include('partials.alert')
            <div class="row gy-3">
                <div class="col-md-6"><label class="form-label">Jenis cuti</label><select name="jenis_cuti" class="form-select" required>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('jenis_cuti', $requestItem->jenis_cuti) == $cat->id)>{{ $cat->nama_kategori }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" value="{{ old('keterangan', $requestItem->keterangan) }}"></div>
                <div class="col-md-6"><label class="form-label">Tanggal mulai</label><input type="date" class="form-control" name="tanggal_awal" value="{{ old('tanggal_awal', optional($requestItem->tanggal_awal)->format('Y-m-d')) }}" required></div>
                <div class="col-md-6"><label class="form-label">Tanggal selesai</label><input type="date" class="form-control" name="tanggal_akhir" value="{{ old('tanggal_akhir', optional($requestItem->tanggal_akhir)->format('Y-m-d')) }}" required></div>
            </div>
            <button class="btn btn-primary-600 mt-24">Kirim</button>
        </form>
    </div>
</div>
@endsection
