@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-24 radius-8" role="alert" id="validation-alert">
  <div class="d-flex align-items-start gap-10">
    <i class="ri-error-warning-line text-xl flex-shrink-0 mt-2"></i>
    <div>
      <h6 class="fw-semibold mb-4 text-sm">Terdapat {{ $errors->count() }} kesalahan pada form:</h6>
      <ul class="mb-0 ps-16 text-sm" style="list-style: disc;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Tab Navigation -->
<ul class="nav nav-tabs nav-bordered mb-0" id="penjadwalanTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active d-flex align-items-center gap-8" id="data-penjadwalan-tab" data-bs-toggle="tab" data-bs-target="#data-penjadwalan" type="button" role="tab" aria-controls="data-penjadwalan" aria-selected="true">
      <i class="ri-calendar-schedule-line text-lg"></i> Data Penjadwalan
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link d-flex align-items-center gap-8" id="jadwal-kursus-tab" data-bs-toggle="tab" data-bs-target="#jadwal-kursus" type="button" role="tab" aria-controls="jadwal-kursus" aria-selected="false">
      <i class="ri-time-line text-lg"></i> Jadwal Kursus
    </button>
  </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="penjadwalanTabsContent">
  <!-- Tab 1: Data Penjadwalan -->
  <div class="tab-pane fade show active" id="data-penjadwalan" role="tabpanel" aria-labelledby="data-penjadwalan-tab">
    <div class="card shadow-1 radius-8 mb-5 border-top-0 rounded-top-0">
      <div class="card-body p-24">
        <div class="row gy-lg-3">

          <!-- Siswa -->
          <div class="col-md-6">
            <label for="detail_pendaftaran_id" class="form-label fw-semibold text-sm">Siswa <span class="text-danger">*</span></label>
            <select name="detail_pendaftaran_id" id="detail_pendaftaran_id" class="form-select @error('detail_pendaftaran_id') is-invalid @enderror" required>
              <option value="">— Pilih Siswa —</option>
              @foreach($siswaList as $item)
                <option value="{{ $item->detail_pendaftaran_id }}" {{ old('detail_pendaftaran_id', $penjadwalan->detail_pendaftaran_id ?? '') == $item->detail_pendaftaran_id ? 'selected' : '' }}>
                  {{ $item->label }}
                </option>
              @endforeach
            </select>
            @error('detail_pendaftaran_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted mt-1 d-block"><i class="ri-information-line"></i> Hanya siswa yang sudah membayar uang muka</small>
          </div>

          <!-- Trainer -->
          <div class="col-md-6">
            <label for="karyawan_id" class="form-label fw-semibold text-sm">Trainer <span class="text-danger">*</span></label>
            <select name="karyawan_id" id="karyawan_id" class="form-select @error('karyawan_id') is-invalid @enderror" required>
              <option value="">— Pilih Trainer —</option>
              @foreach($trainerList as $trainer)
                <option value="{{ $trainer->id }}" {{ old('karyawan_id', $penjadwalan->karyawan_id ?? '') == $trainer->id ? 'selected' : '' }}>
                  {{ $trainer->nama_karyawan }}
                </option>
              @endforeach
            </select>
            @error('karyawan_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <!-- Lokasi -->
          <div class="col-md-6">
            <label for="lokasi" class="form-label fw-semibold text-sm">Lokasi <span class="text-danger">*</span></label>
            <select name="lokasi" id="lokasi" class="form-select @error('lokasi') is-invalid @enderror" required>
              <option value="">— Pilih Lokasi —</option>
              @foreach($lokasiOptions as $key => $label)
                <option value="{{ $key }}" {{ old('lokasi', $penjadwalan->lokasi ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('lokasi')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Jumlah Pertemuan -->
          <div class="col-md-6">
            <label for="jumlah_pertemuan" class="form-label fw-semibold text-sm">Jumlah Pertemuan <span class="text-danger">*</span></label>
            <input type="number" name="jumlah_pertemuan" id="jumlah_pertemuan" class="form-control @error('jumlah_pertemuan') is-invalid @enderror"
                   value="{{ old('jumlah_pertemuan', $penjadwalan->jumlah_pertemuan ?? '') }}" placeholder="Masukkan jumlah pertemuan" min="1" max="999" required>
            @error('jumlah_pertemuan')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Tanggal Mulai -->
          <div class="col-md-6">
            <label for="tgl_mulai" class="form-label fw-semibold text-sm">Tanggal Mulai <span class="text-danger">*</span></label>
            <div class="datepicker-wrapper position-relative" id="datepicker-mulai">
              <input type="text" name="tgl_mulai" id="tgl_mulai" class="form-control pe-40 @error('tgl_mulai') is-invalid @enderror" data-input
                     value="{{ old('tgl_mulai', isset($penjadwalan) && $penjadwalan->tgl_mulai ? \Carbon\Carbon::parse($penjadwalan->tgl_mulai)->format('d/m/Y') : '') }}"
                     placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
              <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
                <i class="ri-calendar-line text-lg"></i>
              </span>
            </div>
            @error('tgl_mulai')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          @if(isset($penjadwalan))
          <!-- Tanggal Selesai -->
          <div class="col-md-6">
            <label for="tgl_selesai" class="form-label fw-semibold text-sm">Tanggal Selesai</label>
            <div class="datepicker-wrapper position-relative" id="datepicker-selesai">
              <input type="text" name="tgl_selesai" id="tgl_selesai" class="form-control pe-40 @error('tgl_selesai') is-invalid @enderror" data-input
                     value="{{ old('tgl_selesai', isset($penjadwalan) && $penjadwalan->tgl_selesai ? \Carbon\Carbon::parse($penjadwalan->tgl_selesai)->format('d/m/Y') : '') }}"
                     placeholder="dd/mm/yyyy" autocomplete="off" readonly>
              <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
                <i class="ri-calendar-line text-lg"></i>
              </span>
            </div>
            @error('tgl_selesai')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <!-- Status Jadwal -->
          <div class="col-md-6">
            <label for="status_jadwal" class="form-label fw-semibold text-sm">Status Jadwal <span class="text-danger">*</span></label>
            <select name="status_jadwal" id="status_jadwal" class="form-select @error('status_jadwal') is-invalid @enderror" required>
              <option value="0" {{ old('status_jadwal', $penjadwalan->status_jadwal ?? '') === 0 || old('status_jadwal', $penjadwalan->status_jadwal ?? '') === '0' ? 'selected' : '' }}>Berjalan</option>
              <option value="1" {{ old('status_jadwal', $penjadwalan->status_jadwal ?? '') === 1 || old('status_jadwal', $penjadwalan->status_jadwal ?? '') === '1' ? 'selected' : '' }}>Selesai</option>
              <option value="2" {{ old('status_jadwal', $penjadwalan->status_jadwal ?? '') === 2 || old('status_jadwal', $penjadwalan->status_jadwal ?? '') === '2' ? 'selected' : '' }}>DO</option>
            </select>
            @error('status_jadwal')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <!-- Keterangan (DO) -->
          <div class="col-md-6" id="keterangan-wrapper" style="{{ old('status_jadwal', $penjadwalan->status_jadwal ?? '') == 2 ? '' : 'display:none;' }}">
            <label for="keterangan" class="form-label fw-semibold text-sm">Keterangan</label>
            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Masukkan keterangan (opsional)">{{ old('keterangan', $penjadwalan->keterangan ?? '') }}</textarea>
            @error('keterangan')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          @endif
        </div>
      </div>
    </div>
    @if(isset($penjadwalan))
      <div class="card shadow-1 radius-8 mb-0 border-top-0 rounded-top-0" id="gbmp-section" style="{{ in_array(old('status_jadwal', $penjadwalan->status_jadwal ?? ''), [1, '1']) ? '' : 'display:none;' }}">
        <div class="card-body p-24">
          <!-- GBMP Section -->
          <div class="d-flex justify-content-between align-items-center mb-16">
            <p class="text-muted mb-0"><i class="ri-file-text-line"></i> Dokumen GBMP (Garis Besar Materi Pengajaran)</p>
            <button type="button" class="btn btn-primary-600 btn-sm d-flex align-items-center gap-1" id="btn-add-gbmp" data-bs-toggle="modal" data-bs-target="#modalGbmp">
              <i class="ri-add-line"></i> Tambah GBMP
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover d-none" id="tableGbmp">
              <thead class="bg-neutral-100">
                <tr>
                  <th class="text-center text-sm fw-semibold" width="60">No</th>
                  <th class="text-sm fw-semibold">Nama GBMP</th>
                  <th class="text-sm fw-semibold">File</th>
                  <th class="text-center text-sm fw-semibold" width="140">Aksi</th>
                </tr>
              </thead>
              <tbody id="gbmp-tbody"></tbody>
            </table>
          </div>

          <div id="no-gbmp-message" class="text-center py-24 text-muted">
            <i class="ri-file-text-line d-block text-9xl mb-8"></i>
            <p class="mb-0">Belum ada dokumen GBMP.</p>
          </div>

          <!-- Hidden input for delete flag -->
          <input type="hidden" name="hapus_gbmp" id="hapus_gbmp" value="0">
        </div>
      </div>
    @endif
  </div>

  <!-- Tab 2: Jadwal Kursus -->
  <div class="tab-pane fade" id="jadwal-kursus" role="tabpanel" aria-labelledby="jadwal-kursus-tab">
    <div class="card shadow-1 radius-8 mb-5 border-top-0 rounded-top-0">
      <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-center mb-16">
          <p class="text-muted mb-0"><i class="ri-information-line"></i> Atur jadwal kursus berdasarkan hari dan jam</p>
          <button type="button" class="btn btn-primary-600 btn-sm d-flex align-items-center gap-1" id="btn-add-jadwal" data-bs-toggle="modal" data-bs-target="#modalJadwal">
            <i class="ri-add-line"></i> Tambah Jadwal
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="tableJadwal">
            <thead class="bg-neutral-100">
              <tr>
                <th class="text-center text-sm fw-semibold" width="60">No</th>
                <th class="text-sm fw-semibold">Hari</th>
                <th class="text-sm fw-semibold">Jam Mulai</th>
                <th class="text-center text-sm fw-semibold" width="120">Aksi</th>
              </tr>
            </thead>
            <tbody id="jadwal-tbody">
              <!-- Data akan di-render via JavaScript -->
            </tbody>
          </table>
        </div>

        <div id="no-jadwal-message" class="text-center py-24 text-muted d-none">
          <i class="ri-calendar-todo-line text-3xl d-block mb-8"></i>
          <p class="mb-0">Belum ada jadwal ditambahkan. Klik tombol "Tambah Jadwal" untuk menambahkan.</p>
        </div>

        <!-- Hidden inputs untuk menyimpan data jadwal -->
        <div id="jadwal-hidden-inputs"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Jadwal Kursus -->
<div class="modal fade" id="modalJadwal" tabindex="-1" aria-labelledby="modalJadwalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content radius-8">
      <div class="modal-header bg-primary-50 py-16 px-24">
        <h6 class="modal-title fw-semibold text-primary-600" id="modalJadwalLabel">
          <i class="ri-calendar-schedule-line me-8"></i> Tambah Jadwal Kursus
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-24">
        <div class="mb-16">
          <label for="modal_hari" class="form-label fw-semibold text-sm">Hari <span class="text-danger">*</span></label>
          <select id="modal_hari" class="form-select">
            <option value="">— Pilih Hari —</option>
            <option value="Senin">Senin</option>
            <option value="Selasa">Selasa</option>
            <option value="Rabu">Rabu</option>
            <option value="Kamis">Kamis</option>
            <option value="Jumat">Jumat</option>
            <option value="Sabtu">Sabtu</option>
            <option value="Minggu">Minggu</option>
          </select>
          <div class="invalid-feedback" id="modal_hari_error"></div>
        </div>
        <div class="mb-0">
          <label for="modal_jam_mulai" class="form-label fw-semibold text-sm">Jam Mulai <span class="text-danger">*</span></label>
          <input type="time" id="modal_jam_mulai" class="form-control">
          <div class="invalid-feedback" id="modal_jam_mulai_error"></div>
        </div>
      </div>
      <div class="modal-footer bg-neutral-50 py-12 px-24">
        <button type="button" class="btn btn-outline-neutral-600" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary-600" id="btn-save-jadwal">
          <i class="ri-save-line me-4"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal GBMP -->
<div class="modal fade" id="modalGbmp" tabindex="-1" aria-labelledby="modalGbmpLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content radius-8">
      <div class="modal-header bg-primary-50 py-16 px-24">
        <h6 class="modal-title fw-semibold text-primary-600" id="modalGbmpLabel">
          <i class="ri-file-text-line me-8"></i> Upload GBMP
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-24">
        <div class="mb-16">
          <label for="modal_nama_gbmp" class="form-label fw-semibold text-sm">Nama GBMP <span class="text-danger">*</span></label>
          <input type="text" id="modal_nama_gbmp" class="form-control" placeholder="Masukkan nama GBMP" maxlength="255">
          <div class="invalid-feedback" id="modal_nama_gbmp_error"></div>
        </div>
        <div class="mb-0">
          <label for="modal_upload_gbmp" class="form-label fw-semibold text-sm">File GBMP <span class="text-danger">*</span></label>
          <input type="file" id="modal_upload_gbmp" class="form-control" accept=".pdf,.jpg,.jpeg">
          <div class="invalid-feedback" id="modal_upload_gbmp_error"></div>
          <small class="text-muted mt-1 d-block"><i class="ri-information-line"></i> Format: PDF, JPG, JPEG (maks. 1MB)</small>
        </div>
      </div>
      <div class="modal-footer bg-neutral-50 py-12 px-24">
        <button type="button" class="btn btn-outline-neutral-600" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary-600" id="btn-save-gbmp">
          <i class="ri-save-line me-4"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Status Jadwal - Show/Hide Keterangan & GBMP (edit only)
  @if(isset($penjadwalan))
  document.getElementById('status_jadwal').addEventListener('change', function () {
    const keteranganWrapper = document.getElementById('keterangan-wrapper');
    const gbmpSection = document.getElementById('gbmp-section');

    // Reset visibility
    keteranganWrapper.style.display = 'none';
    document.getElementById('keterangan').value = '';
    if (gbmpSection) gbmpSection.style.display = 'none';

    if (this.value === '2') {
      // DO → show keterangan
      keteranganWrapper.style.display = '';
    } else if (this.value === '1') {
      // Selesai → show GBMP
      if (gbmpSection) gbmpSection.style.display = '';
    }
  });
  @endif

  // Jadwal Kursus Management
  document.addEventListener('DOMContentLoaded', function() {
    // Load existing jadwal data
    let jadwalData = [];
    @php
      $existingJadwal = old('jadwal', []);
      if (empty($existingJadwal) && isset($penjadwalan) && $penjadwalan->detailPenjadwalan->count() > 0) {
          $existingJadwal = $penjadwalan->detailPenjadwalan->map(function($item) {
              return ['hari' => $item->hari, 'jam_mulai' => $item->jam_mulai];
          })->toArray();
      }
    @endphp
    jadwalData = @json(array_values($existingJadwal));

    let editingIndex = -1;
    const modalJadwal = document.getElementById('modalJadwal');
    const modalTitle = document.getElementById('modalJadwalLabel');
    const btnSaveJadwal = document.getElementById('btn-save-jadwal');
    const modalHari = document.getElementById('modal_hari');
    const modalJamMulai = document.getElementById('modal_jam_mulai');
    const jadwalTbody = document.getElementById('jadwal-tbody');
    const noJadwalMsg = document.getElementById('no-jadwal-message');
    const hiddenInputsContainer = document.getElementById('jadwal-hidden-inputs');

    // Render table
    function renderTable() {
      jadwalTbody.innerHTML = '';
      hiddenInputsContainer.innerHTML = '';

      if (jadwalData.length === 0) {
        noJadwalMsg.classList.remove('d-none');
        document.getElementById('tableJadwal').classList.add('d-none');
      } else {
        noJadwalMsg.classList.add('d-none');
        document.getElementById('tableJadwal').classList.remove('d-none');

        jadwalData.forEach((item, index) => {
          // Table row
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="text-center text-sm">${index + 1}</td>
            <td class="text-sm">${item.hari}</td>
            <td class="text-sm">${item.jam_mulai}</td>
            <td class="text-center">
              <div class="d-inline-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary-600 btn-edit-jadwal" data-index="${index}" title="Edit">
                  <i class="ri-edit-line"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger-600 btn-delete-jadwal" data-index="${index}" title="Hapus">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            </td>
          `;
          jadwalTbody.appendChild(tr);

          // Hidden inputs
          hiddenInputsContainer.innerHTML += `
            <input type="hidden" name="jadwal[${index}][hari]" value="${item.hari}">
            <input type="hidden" name="jadwal[${index}][jam_mulai]" value="${item.jam_mulai}">
          `;
        });
      }
    }

    // Reset modal
    function resetModal() {
      modalHari.value = '';
      modalJamMulai.value = '';
      modalHari.classList.remove('is-invalid');
      modalJamMulai.classList.remove('is-invalid');
      editingIndex = -1;
      modalTitle.innerHTML = '<i class="ri-calendar-schedule-line me-8"></i> Tambah Jadwal Kursus';
    }

    // Open modal for add
    document.getElementById('btn-add-jadwal').addEventListener('click', function() {
      resetModal();
    });

    // Open modal for edit
    jadwalTbody.addEventListener('click', function(e) {
      const editBtn = e.target.closest('.btn-edit-jadwal');
      const deleteBtn = e.target.closest('.btn-delete-jadwal');

      if (editBtn) {
        const index = parseInt(editBtn.dataset.index);
        editingIndex = index;
        modalHari.value = jadwalData[index].hari;
        modalJamMulai.value = jadwalData[index].jam_mulai;
        modalTitle.innerHTML = '<i class="ri-edit-line me-8"></i> Edit Jadwal Kursus';
        new bootstrap.Modal(modalJadwal).show();
      }

      if (deleteBtn) {
        const index = parseInt(deleteBtn.dataset.index);
        const hariName = jadwalData[index].hari;
        Swal.fire({
          html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
             + '<div class="delete-title">Hapus Jadwal Kursus</div>'
             + '<div class="delete-text">Anda yakin ingin menghapus jadwal hari <strong>' + hariName + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
             + '<div class="delete-actions">'
             + '  <button type="button" class="btn-delete-cancel" id="swal-jadwal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
             + '  <button type="button" class="btn-delete-confirm" id="swal-jadwal-confirm"><i class="ri-delete-bin-6-line"></i> Ya, Hapus</button>'
             + '</div>',
          showConfirmButton: false,
          showCancelButton: false,
          showCloseButton: false,
          customClass: { popup: 'delete-popup' },
          didOpen: function(popup) {
            popup.querySelector('#swal-jadwal-cancel').addEventListener('click', function() { Swal.close(); });
            popup.querySelector('#swal-jadwal-confirm').addEventListener('click', function() {
              jadwalData.splice(index, 1);
              renderTable();
              Swal.close();
            });
          }
        });
      }
    });

    // Save jadwal (add or update)
    btnSaveJadwal.addEventListener('click', function() {
      let isValid = true;

      // Validation
      if (!modalHari.value) {
        modalHari.classList.add('is-invalid');
        document.getElementById('modal_hari_error').textContent = 'Hari wajib dipilih';
        isValid = false;
      } else {
        modalHari.classList.remove('is-invalid');
      }

      if (!modalJamMulai.value) {
        modalJamMulai.classList.add('is-invalid');
        document.getElementById('modal_jam_mulai_error').textContent = 'Jam mulai wajib diisi';
        isValid = false;
      } else {
        modalJamMulai.classList.remove('is-invalid');
      }

      if (!isValid) return;

      const newItem = {
        hari: modalHari.value,
        jam_mulai: modalJamMulai.value
      };

      if (editingIndex >= 0) {
        // Update existing
        jadwalData[editingIndex] = newItem;
      } else {
        // Add new
        jadwalData.push(newItem);
      }

      renderTable();
      bootstrap.Modal.getInstance(modalJadwal).hide();
      resetModal();
    });

    // Reset on modal close
    modalJadwal.addEventListener('hidden.bs.modal', function() {
      resetModal();
    });

    // Initial render
    renderTable();

    // ==========================================
    // GBMP Management
    // ==========================================
    const tableGbmp = document.getElementById('tableGbmp');
    const gbmpTbody = document.getElementById('gbmp-tbody');
    const noGbmpMsg = document.getElementById('no-gbmp-message');
    const btnAddGbmp = document.getElementById('btn-add-gbmp');
    const modalGbmp = document.getElementById('modalGbmp');
    const modalNamaGbmp = document.getElementById('modal_nama_gbmp');
    const modalUploadGbmp = document.getElementById('modal_upload_gbmp');
    const btnSaveGbmp = document.getElementById('btn-save-gbmp');
    const hapusGbmpInput = document.getElementById('hapus_gbmp');

    // State
    let gbmpData = {
      nama: '',
      fileName: '',
      fileObj: null,
      existingUrl: '',
      isExisting: false
    };

    // Load existing GBMP data
    @php
      $existingGbmp = null;
      if (isset($penjadwalan) && $penjadwalan->nama_gbmp) {
          $existingGbmp = [
              'nama' => $penjadwalan->nama_gbmp,
              'fileName' => basename($penjadwalan->upload_gbmp ?? ''),
              'url' => $penjadwalan->upload_gbmp ? asset('storage/' . $penjadwalan->upload_gbmp) : '',
          ];
      }
    @endphp
    @if($existingGbmp)
      gbmpData.nama = @json($existingGbmp['nama']);
      gbmpData.fileName = @json($existingGbmp['fileName']);
      gbmpData.existingUrl = @json($existingGbmp['url']);
      gbmpData.isExisting = true;
    @endif

    function renderGbmpTable() {
      gbmpTbody.innerHTML = '';

      if (!gbmpData.nama && !gbmpData.fileName) {
        tableGbmp.classList.add('d-none');
        noGbmpMsg.classList.remove('d-none');
        btnAddGbmp.classList.remove('d-none');
        btnAddGbmp.innerHTML = '<i class="ri-add-line"></i> Tambah GBMP';
      } else {
        tableGbmp.classList.remove('d-none');
        noGbmpMsg.classList.add('d-none');
        btnAddGbmp.classList.add('d-none');

        const previewUrl = gbmpData.isExisting ? gbmpData.existingUrl : (gbmpData.fileObj ? URL.createObjectURL(gbmpData.fileObj) : '#');

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="text-center text-sm">1</td>
          <td class="text-sm">${gbmpData.nama}</td>
          <td class="text-sm"><i class="ri-file-text-line me-4"></i>${gbmpData.fileName}</td>
          <td class="text-center">
            <div class="d-inline-flex align-items-center gap-2">
              <a href="${previewUrl}" target="_blank" class="btn btn-sm btn-outline-info-600" title="Preview">
                <i class="ri-eye-line"></i>
              </a>
              <button type="button" class="btn btn-sm btn-outline-danger-600 btn-delete-gbmp" title="Hapus">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </td>
        `;
        gbmpTbody.appendChild(tr);
      }

      // Sync hidden inputs for nama_gbmp
      let existingNamaInput = document.querySelector('input[name="nama_gbmp"]');
      if (!existingNamaInput) {
        existingNamaInput = document.createElement('input');
        existingNamaInput.type = 'hidden';
        existingNamaInput.name = 'nama_gbmp';
        document.getElementById('jadwal-hidden-inputs').parentNode.appendChild(existingNamaInput);
      }
      existingNamaInput.value = gbmpData.nama;
    }

    // Reset GBMP modal
    function resetGbmpModal() {
      modalNamaGbmp.value = '';
      modalUploadGbmp.value = '';
      modalNamaGbmp.classList.remove('is-invalid');
      modalUploadGbmp.classList.remove('is-invalid');
    }

    // Open modal
    btnAddGbmp.addEventListener('click', function() {
      resetGbmpModal();
      if (gbmpData.nama) {
        modalNamaGbmp.value = gbmpData.nama;
      }
    });

    // Save GBMP from modal
    btnSaveGbmp.addEventListener('click', function() {
      let isValid = true;

      if (!modalNamaGbmp.value.trim()) {
        modalNamaGbmp.classList.add('is-invalid');
        document.getElementById('modal_nama_gbmp_error').textContent = 'Nama GBMP wajib diisi';
        isValid = false;
      } else {
        modalNamaGbmp.classList.remove('is-invalid');
      }

      const hasNewFile = modalUploadGbmp.files && modalUploadGbmp.files.length > 0;
      const hasExisting = gbmpData.isExisting && gbmpData.fileName;

      if (!hasNewFile && !hasExisting) {
        modalUploadGbmp.classList.add('is-invalid');
        document.getElementById('modal_upload_gbmp_error').textContent = 'File GBMP wajib diunggah';
        isValid = false;
      } else if (hasNewFile) {
        const file = modalUploadGbmp.files[0];
        const allowedExts = ['pdf','jpg','jpeg'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExts.includes(ext)) {
          modalUploadGbmp.classList.add('is-invalid');
          document.getElementById('modal_upload_gbmp_error').textContent = 'Format file tidak didukung (PDF, JPG, JPEG)';
          isValid = false;
        } else if (file.size > 1024 * 1024) { 
          modalUploadGbmp.classList.add('is-invalid');
          document.getElementById('modal_upload_gbmp_error').textContent = 'Ukuran file maksimal 1MB';
          isValid = false;
        } else {
          modalUploadGbmp.classList.remove('is-invalid');
        }
      } else {
        modalUploadGbmp.classList.remove('is-invalid');
      }

      if (!isValid) return;

      gbmpData.nama = modalNamaGbmp.value.trim();

      if (hasNewFile) {
        const file = modalUploadGbmp.files[0];
        gbmpData.fileName = file.name;
        gbmpData.fileObj = file;
        gbmpData.isExisting = false;

        // Transfer file to the actual form file input
        let formFileInput = document.getElementById('form_upload_gbmp');
        if (!formFileInput) {
          formFileInput = document.createElement('input');
          formFileInput.type = 'file';
          formFileInput.name = 'upload_gbmp';
          formFileInput.id = 'form_upload_gbmp';
          formFileInput.style.display = 'none';
          document.getElementById('jadwal-hidden-inputs').parentNode.appendChild(formFileInput);
        }
        // Use DataTransfer to set file on the hidden input
        const dt = new DataTransfer();
        dt.items.add(file);
        formFileInput.files = dt.files;
      }

      hapusGbmpInput.value = '0';
      renderGbmpTable();
      bootstrap.Modal.getInstance(modalGbmp).hide();
      resetGbmpModal();
    });

    // Delete GBMP
    gbmpTbody.addEventListener('click', function(e) {
      const deleteBtn = e.target.closest('.btn-delete-gbmp');
      if (deleteBtn) {
        Swal.fire({
          html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
             + '<div class="delete-title">Hapus Dokumen GBMP</div>'
             + '<div class="delete-text">Anda yakin ingin menghapus dokumen GBMP <strong>' + gbmpData.nama + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
             + '<div class="delete-actions">'
             + '  <button type="button" class="btn-delete-cancel" id="swal-gbmp-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
             + '  <button type="button" class="btn-delete-confirm" id="swal-gbmp-confirm"><i class="ri-delete-bin-6-line"></i> Ya, Hapus</button>'
             + '</div>',
          showConfirmButton: false,
          showCancelButton: false,
          showCloseButton: false,
          customClass: { popup: 'delete-popup' },
          didOpen: function(popup) {
            popup.querySelector('#swal-gbmp-cancel').addEventListener('click', function() { Swal.close(); });
            popup.querySelector('#swal-gbmp-confirm').addEventListener('click', function() {
              gbmpData = { nama: '', fileName: '', fileObj: null, existingUrl: '', isExisting: false };
              hapusGbmpInput.value = '1';
              // Remove file input
              const formFileInput = document.getElementById('form_upload_gbmp');
              if (formFileInput) formFileInput.remove();
              renderGbmpTable();
              Swal.close();
            });
          }
        });
      }
    });

    // Reset on modal close
    modalGbmp.addEventListener('hidden.bs.modal', function() {
      resetGbmpModal();
    });

    // Initial GBMP render
    renderGbmpTable();
  });
</script>

<style>
.swal2-popup.delete-popup {
  border-radius: 16px;
  padding: 2rem 1.5rem 1.5rem;
  max-width: 400px;
  box-shadow: 0 20px 60px rgba(0,0,0,.15);
  border: 1px solid rgba(0,0,0,.05);
}
.delete-icon-wrapper {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
}
.delete-icon-wrapper i {
  font-size: 32px;
  color: #DC2626;
}
.swal2-popup.delete-popup .swal2-html-container {
  margin: 0;
  padding: 0;
}
.delete-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1B2559;
  margin-bottom: .375rem;
}
.delete-text {
  font-size: .8125rem;
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}
.delete-text strong {
  color: #334155;
  font-weight: 600;
}
.delete-actions {
  display: flex;
  gap: .75rem;
}
.delete-actions .btn-delete-cancel,
.delete-actions .btn-delete-confirm {
  flex: 1;
  padding: .6rem 1rem;
  border-radius: 10px;
  font-size: .8125rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .4rem;
  transition: all .2s ease;
}
.delete-actions .btn-delete-cancel {
  background: #f1f5f9;
  color: #475569;
}
.delete-actions .btn-delete-cancel:hover {
  background: #e2e8f0;
  color: #1e293b;
}
.delete-actions .btn-delete-confirm {
  background: #DC2626;
  color: #fff;
  box-shadow: 0 4px 12px rgba(220,38,38,.3);
}
.delete-actions .btn-delete-confirm:hover {
  background: #B91C1C;
  box-shadow: 0 4px 16px rgba(220,38,38,.4);
  transform: translateY(-1px);
}
[data-theme="dark"] .swal2-popup.delete-popup {
  background: #1e293b;
  border-color: rgba(255,255,255,.08);
}
[data-theme="dark"] .delete-title { color: #e2e8f0; }
[data-theme="dark"] .delete-text { color: #94a3b8; }
[data-theme="dark"] .delete-text strong { color: #cbd5e1; }
[data-theme="dark"] .delete-icon-wrapper {
  background: linear-gradient(135deg, rgba(220,38,38,.15) 0%, rgba(220,38,38,.25) 100%);
}
[data-theme="dark"] .delete-actions .btn-delete-cancel {
  background: #334155;
  color: #cbd5e1;
}
[data-theme="dark"] .delete-actions .btn-delete-cancel:hover {
  background: #475569;
  color: #f1f5f9;
}
</style>
