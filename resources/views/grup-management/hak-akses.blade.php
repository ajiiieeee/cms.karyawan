@extends('layouts.main')

@section('title', 'Hak Akses - ' . $grup->nama_grup)

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Hak Akses: {{ $grup->nama_grup }}</h6>
        <p class="text-neutral-600 mt-4 mb-0">Settings &raquo; Grup Management &raquo; Hak Akses</p>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-16" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('grup-management.hak-akses.update', \App\Helpers\IdEncryptor::encrypt($grup->id)) }}" method="POST" id="form-hak-akses">
      @csrf
      @method('PUT')

      <div class="card shadow-1 radius-8">
        <div class="card-body p-24">
          <div class="d-flex align-items-center justify-content-between mb-20">
            <div>
              <h6 class="fw-semibold text-md mb-4">Pengaturan Hak Akses Menu</h6>
              <p class="text-neutral-500 text-sm mb-0">Centang akses yang diizinkan untuk grup <strong>{{ $grup->nama_grup }}</strong></p>
            </div>
            <div class="form-check d-flex align-items-center">
              <label class="form-check-label fw-semibold text-sm" for="check-all-global">Pilih Semua</label>
              <input class="form-check-input" type="checkbox" id="check-all-global">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
              <thead class="bg-neutral-50">
                <tr>
                  <th class="fw-semibold text-sm" width="40%">Menu</th>
                  <th class="fw-semibold text-sm text-center" width="15%">View</th>
                  <th class="fw-semibold text-sm text-center" width="15%">Add</th>
                  <th class="fw-semibold text-sm text-center" width="15%">Edit</th>
                  <th class="fw-semibold text-sm text-center" width="15%">Delete</th>
                </tr>
              </thead>
              <tbody>
                @foreach($parentMenus as $parent)
                  @php
                    $parentAccess = $existingAccess->get($parent->id);
                    $children = $childMenus->get($parent->id, collect());
                    $hasChildren = $children->isNotEmpty();
                  @endphp
                  @if($hasChildren)
                  <tr class="bg-neutral-50">
                    <td class="fw-semibold text-neutral-500">
                      @if($parent->icon)<i class="{{ $parent->icon }} me-2"></i>@endif
                      {{ $parent->nama_menu }}
                    </td>
                    <td class="text-center text-muted"></td>
                    <td class="text-center text-muted"></td>
                    <td class="text-center text-muted"></td>
                    <td class="text-center text-muted"></td>
                  </tr>
                  @foreach($children as $child)
                    @php
                      $childAccess = $existingAccess->get($child->id);
                      $grandChildren = $childMenus->get($child->id, collect());
                      $isSubParent = $grandChildren->isNotEmpty();
                    @endphp
                    @if($isSubParent)
                    <tr class="bg-neutral-100">
                      <td class="fw-semibold ps-40">
                        <i class="ri-corner-down-right-line me-1 text-neutral-400"></i>
                        @if($child->icon)<i class="{{ $child->icon }} me-1"></i>@endif
                        {{ $child->nama_menu }}
                      </td>
                      <td class="text-center text-muted"></td>
                      <td class="text-center text-muted"></td>
                      <td class="text-center text-muted"></td>
                      <td class="text-center text-muted"></td>
                    </tr>
                    @foreach($grandChildren as $gc)
                      @php $gcAccess = $existingAccess->get($gc->id); @endphp
                      <tr>
                        <td class="ps-64">
                          <i class="ri-more-fill me-1 text-neutral-400"></i>
                          @if($gc->icon)<i class="{{ $gc->icon }} me-1"></i>@endif
                          {{ $gc->nama_menu }}
                        </td>
                        <td class="text-center">
                          <input type="checkbox" class="form-check-input akses-check sub-child-check" name="akses[{{ $gc->id }}][view]" value="1"
                                 {{ $gcAccess && $gcAccess->view ? 'checked' : '' }} data-action="view">
                        </td>
                        <td class="text-center">
                          <input type="checkbox" class="form-check-input akses-check sub-child-check" name="akses[{{ $gc->id }}][add]" value="1"
                                 {{ $gcAccess && $gcAccess->add ? 'checked' : '' }} data-action="add">
                        </td>
                        <td class="text-center">
                          <input type="checkbox" class="form-check-input akses-check sub-child-check" name="akses[{{ $gc->id }}][edit]" value="1"
                                 {{ $gcAccess && $gcAccess->edit ? 'checked' : '' }} data-action="edit">
                        </td>
                        <td class="text-center">
                          <input type="checkbox" class="form-check-input akses-check sub-child-check" name="akses[{{ $gc->id }}][delete]" value="1"
                                 {{ $gcAccess && $gcAccess->delete ? 'checked' : '' }} data-action="delete">
                        </td>
                      </tr>
                    @endforeach
                    @else
                    <tr>
                      <td class="ps-40">
                        <i class="ri-corner-down-right-line me-1 text-neutral-400"></i>
                        @if($child->icon)<i class="{{ $child->icon }} me-1"></i>@endif
                        {{ $child->nama_menu }}
                      </td>
                      <td class="text-center">
                        <input type="checkbox" class="form-check-input akses-check child-check" name="akses[{{ $child->id }}][view]" value="1"
                               {{ $childAccess && $childAccess->view ? 'checked' : '' }} data-action="view" data-menu-id="{{ $child->id }}">
                      </td>
                      <td class="text-center">
                        <input type="checkbox" class="form-check-input akses-check child-check" name="akses[{{ $child->id }}][add]" value="1"
                               {{ $childAccess && $childAccess->add ? 'checked' : '' }} data-action="add" data-menu-id="{{ $child->id }}">
                      </td>
                      <td class="text-center">
                        <input type="checkbox" class="form-check-input akses-check child-check" name="akses[{{ $child->id }}][edit]" value="1"
                               {{ $childAccess && $childAccess->edit ? 'checked' : '' }} data-action="edit" data-menu-id="{{ $child->id }}">
                      </td>
                      <td class="text-center">
                        <input type="checkbox" class="form-check-input akses-check child-check" name="akses[{{ $child->id }}][delete]" value="1"
                               {{ $childAccess && $childAccess->delete ? 'checked' : '' }} data-action="delete" data-menu-id="{{ $child->id }}">
                      </td>
                    </tr>
                    @endif
                  @endforeach
                  @else
                  <tr class="bg-neutral-50">
                    <td class="fw-semibold">
                      @if($parent->icon)<i class="{{ $parent->icon }} me-2"></i>@endif
                      {{ $parent->nama_menu }}
                    </td>
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input akses-check" name="akses[{{ $parent->id }}][view]" value="1"
                             {{ $parentAccess && $parentAccess->view ? 'checked' : '' }} data-action="view">
                    </td>
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input akses-check" name="akses[{{ $parent->id }}][add]" value="1"
                             {{ $parentAccess && $parentAccess->add ? 'checked' : '' }} data-action="add">
                    </td>
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input akses-check" name="akses[{{ $parent->id }}][edit]" value="1"
                             {{ $parentAccess && $parentAccess->edit ? 'checked' : '' }} data-action="edit">
                    </td>
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input akses-check" name="akses[{{ $parent->id }}][delete]" value="1"
                             {{ $parentAccess && $parentAccess->delete ? 'checked' : '' }} data-action="delete">
                    </td>
                  </tr>
                  @endif
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end gap-8 mt-24">
            <a href="{{ route('grup-management.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
            <button type="submit" class="btn btn-primary-600 btn-submit">
              <span class="btn-text">Simpan Hak Akses</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
          </div>
        </div>
      </div>
    </form>
@endsection

@push('styles')
<style>
.akses-check {
    width: 18px;
    height: 18px;
    border: 1px solid #6b7280 !important;
    cursor: pointer;
}
.akses-check:checked {
    background-color: #4f46e5;
    border-color: #4f46e5 !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#check-all-global').on('change', function() {
        $('.akses-check').prop('checked', $(this).is(':checked'));
    });

    function updateGlobalCheck() {
        var total = $('.akses-check').length;
        var checked = $('.akses-check:checked').length;
        $('#check-all-global').prop('checked', total > 0 && total === checked);
    }

    $('.akses-check').on('change', function() {
        updateGlobalCheck();
    });

    updateGlobalCheck();

    $('#form-hak-akses').on('submit', function() {
        var btn = $(this).find('.btn-submit');
        btn.prop('disabled', true);
        btn.find('.btn-text').text('Menyimpan...');
        btn.find('.spinner-border').removeClass('d-none');
    });
});
</script>
@endpush
