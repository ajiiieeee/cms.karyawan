@extends('layouts.main')
@section('title','Pengumuman')
@section('content')
<div class="breadcrumb mb-24"><h6 class="fw-semibold mb-0">Pengumuman</h6><p class="text-neutral-600 mt-4 mb-0">Informasi terbaru untuk karyawan.</p></div><div class="card"><div class="card-body">@forelse($announcements as $item)<article class="border-bottom pb-20 mb-20"><h6>{{ $item->title }}</h6><small class="text-secondary-light">{{ $item->published_at?->format('d M Y, H:i') }}</small><p class="mt-12 mb-0">{{ $item->content }}</p></article>@empty<p class="text-secondary-light">Belum ada pengumuman aktif.</p>@endforelse{{ $announcements->links() }}</div></div>
@endsection
