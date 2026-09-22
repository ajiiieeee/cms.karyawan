@extends('layouts.main')

@section('title', 'FIMP')

@section('content')
<div class="card">
    <div class="card-header"><h5>{{ $requestItem->exists ? 'Ubah' : 'Buat' }} FIMP</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ $requestItem->exists ? route('fimp.update', $requestItem) : route('fimp.store') }}">
            @csrf
            @if($requestItem->exists) @method('PUT') @endif
            @include('partials.alert')
            <div class="row gy-3">
                <div class="col-md-6"><label class="form-label">Tanggal mulai</label><input type="date" name="tanggal_awal" class="form-control" value="{{ old('tanggal_awal', optional($requestItem->tanggal_awal)->format('Y-m-d')) }}" required></div>
                <div class="col-md-6"><label class="form-label">Tanggal selesai</label><input type="date" name="tanggal_akhir" class="form-control" value="{{ old('tanggal_akhir', optional($requestItem->tanggal_akhir)->format('Y-m-d')) }}" required></div>
                <div class="col-md-6"><label class="form-label">Karyawan pengganti</label><select class="form-select" name="karyawan_pengganti" required>@foreach($substitutes as $sub)<option value="{{ $sub->id }}" @selected(old('karyawan_pengganti', $requestItem->karyawan_pengganti) == $sub->id)>{{ $sub->nama_karyawan }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Keperluan</label><textarea class="form-control" name="keperluan" required>{{ old('keperluan', $requestItem->keperluan) }}</textarea></div>
            </div>
            <button class="btn btn-primary-600 mt-24">Kirim</button>
        </form>
    </div>
</div>
@endsection
