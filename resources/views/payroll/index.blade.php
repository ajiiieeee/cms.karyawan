@extends('layouts.main')
@section('title','Penggajian')
@section('content')
<div class="breadcrumb mb-24"><h6 class="fw-semibold mb-0">Penggajian</h6><p class="text-neutral-600 mt-4 mb-0">Slip gaji dan riwayat pembayaran Anda.</p></div><div class="card"><div class="card-header"><h6 class="mb-0">Slip Gaji</h6></div><div class="card-body">@if($payrolls->isEmpty())<div class="text-center py-32"><i class="ri-wallet-3-line text-4xl text-secondary-light"></i><p class="mt-12 mb-0">Slip gaji belum tersedia.</p></div>@endif</div></div>
@endsection
