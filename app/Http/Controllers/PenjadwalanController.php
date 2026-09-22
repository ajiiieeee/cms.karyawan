<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\DetailPendaftaran;
use App\Models\DetailPenjadwalan;
use App\Models\Karyawan;
use App\Models\Pembayaran;
use App\Models\Penjadwalan;
use App\Models\Sertifikat;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenjadwalanController extends Controller
{
    private array $lokasiOptions = [
        'Tubanan' => 'Tubanan',
        'Nginden' => 'Nginden',
        'Private' => 'Private',
        'Online'  => 'Online',
    ];

    public function index(): View
    {
        return view('penjadwalan.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['tgl_mulai', 'siswa_id', 'karyawan_id', 'lokasi', 'jumlah_pertemuan', 'tgl_selesai'];
        $orderColumn     = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = Penjadwalan::count();

        $query = Penjadwalan::with(['siswa', 'karyawan']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sq) use ($search) {
                    $sq->where('nama_siswa', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
                })
                ->orWhereHas('karyawan', function ($sq) use ($search) {
                    $sq->where('nama_karyawan', 'like', "%{$search}%");
                })
                ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $penjadwalans = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();

        $data = $penjadwalans->map(function ($jadwal) use (&$no, $currentUser) {
            $no++;
            $encId = IdEncryptor::encrypt($jadwal->id);

            $showBtn = '';
            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('penjadwalan', 'view')) {
                $showBtn = '<a href="' . route('penjadwalan.show', $encId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Detail</a>';
            }

            if ($currentUser->hasMenuAccess('penjadwalan', 'edit')) {
                $editBtn = '<a href="' . route('penjadwalan.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('penjadwalan', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($jadwal->siswa->nama_siswa ?? '-') . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            $statusJadwal = match((int) $jadwal->status_jadwal) {
                0 => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-info">Berjalan</span>',
                1 => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-success">Selesai</span>',
                2 => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-warning">DO</span>',
                default => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-secondary">Unknown</span>',
            };

            return [
                'no'                => $no,
                'nama_siswa'        => e($jadwal->siswa->nama_siswa ?? '-'),
                'nama_trainer'      => e($jadwal->karyawan->nama_karyawan ?? '-'),
                'bidang_studi'      => e(($jadwal->bidangStudi->nama_bidang_studi ?? '') . ' - ' . ($jadwal->levelKelas->nama_level ?? '')),
                'lokasi'            => e($jadwal->lokasi),
                'status_jadwal'     => $statusJadwal,
                'aksi'              => '<div class="d-inline-flex align-items-center gap-8">' . $showBtn . $editBtn . $deleteBtn . '</div>',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data->values(),
        ]);
    }

    public function create(): View
    {
        $lokasiOptions = $this->lokasiOptions;

        // Cache the full base Pembayaran list (no scheduled filter)
        $siswaBaseList = Cache::remember('form:penjadwalan:siswa_base', 300,
            fn() => $this->buildSiswaBaseList()
        );

        // Cache all scheduled detail IDs
        $scheduledDetailIds = Cache::remember('penjadwalan:scheduled_detail_ids', 300, fn() =>
            Penjadwalan::whereNotNull('detail_pendaftaran_id')
                ->pluck('detail_pendaftaran_id')
                ->toArray()
        );

        // Filter in-memory: exclude already-scheduled entries
        $siswaList = collect($siswaBaseList)
            ->filter(fn($item) => !in_array($item->detail_pendaftaran_id, $scheduledDetailIds))
            ->sortBy('label')
            ->values();

        // Karyawan dengan jabatan trainer
        $trainerList = Cache::remember('master:karyawan:trainers', 86400, fn() =>
            Karyawan::where('jabatan', 'trainer')
                ->where('status_akun', 'aktif')
                ->orderBy('nama_karyawan')
                ->get()
        );

        return view('penjadwalan.create', compact('lokasiOptions', 'siswaList', 'trainerList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'detail_pendaftaran_id' => ['required', 'integer', 'exists:detail_pendaftaran,id'],
            'karyawan_id'           => ['required', 'integer', 'exists:karyawans,id'],
            'lokasi'                => ['required', Rule::in(array_keys($this->lokasiOptions))],
            'jumlah_pertemuan'      => ['required', 'integer', 'min:1', 'max:999'],
            'tgl_mulai'             => ['required', 'date_format:d/m/Y'],
            'jadwal'                => ['nullable', 'array'],
            'jadwal.*.hari'         => ['nullable', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jadwal.*.jam_mulai'    => ['nullable', 'date_format:H:i,H:i:s'],
        ], $this->validationMessages());

        $tglMulai = Carbon::createFromFormat('d/m/Y', $validated['tgl_mulai']);

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;

            $detail = DetailPendaftaran::findOrFail($validated['detail_pendaftaran_id']);

            // Update status siswa menjadi aktif
            $siswa = Siswa::find($detail->siswa_id);
            if ($siswa && $siswa->status_siswa != 1) {
                $siswa->update(['status_siswa' => 1]);
            }

            // Cari pembayaran terkait
            $pembayaran = Pembayaran::where('detail_pendaftaran_id', $detail->id)
                ->where('uang_muka', '>', 0)
                ->orWhere('pelunasan', '>', 0)
                ->firstOrFail();

            $penjadwalan = Penjadwalan::create([
                'pembayaran_id'         => $pembayaran->id,
                'siswa_id'              => $detail->siswa_id,
                'karyawan_id'           => $validated['karyawan_id'],
                'detail_pendaftaran_id' => $detail->id,
                'bidang_studi_id'       => $detail->bidang_studi_id,
                'level_kelas_id'        => $detail->level_kelas_id,
                'lokasi'                => $validated['lokasi'],
                'jumlah_pertemuan'      => $validated['jumlah_pertemuan'],
                'tgl_mulai'             => $tglMulai->toDateString(),
                'created_by'            => $userName,
                'updated_by'            => $userName,
                'status_jadwal'         => 0,
            ]);

            // Simpan detail jadwal kursus
            if (!empty($validated['jadwal'])) {
                foreach ($validated['jadwal'] as $jadwal) {
                    if (!empty($jadwal['hari']) && !empty($jadwal['jam_mulai'])) {
                        DetailPenjadwalan::create([
                            'penjadwalan_id' => $penjadwalan->id,
                            'hari'           => $jadwal['hari'],
                            'jam_mulai'      => $jadwal['jam_mulai'],
                        ]);
                    }
                }
            }

            DB::commit();
            Cache::forget('penjadwalan:scheduled_detail_ids');
            Cache::forget('form:penjadwalan:siswa_base');
            return redirect()->route('penjadwalan.index')
                ->with('success', 'Data penjadwalan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Penjadwalan store error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data penjadwalan.');
        }
    }

    public function show(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $penjadwalan = Penjadwalan::with(['siswa', 'karyawan', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas', 'detailPenjadwalan'])->findOrFail($id);

        return view('penjadwalan.show', compact('penjadwalan'));
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $penjadwalan = Penjadwalan::with(['siswa', 'karyawan', 'detailPenjadwalan'])->findOrFail($id);
        $lokasiOptions = $this->lokasiOptions;

        // Use same cached base list
        $siswaBaseList = Cache::remember('form:penjadwalan:siswa_base', 300,
            fn() => $this->buildSiswaBaseList()
        );

        // Cache all scheduled detail IDs
        $scheduledDetailIds = Cache::remember('penjadwalan:scheduled_detail_ids', 300, fn() =>
            Penjadwalan::whereNotNull('detail_pendaftaran_id')
                ->pluck('detail_pendaftaran_id')
                ->toArray()
        );

        // Filter in-memory: allow the currently-edited entry back in; exclude others
        $currentDetailId = $penjadwalan->detail_pendaftaran_id;
        $siswaList = collect($siswaBaseList)
            ->filter(fn($item) =>
                $item->detail_pendaftaran_id == $currentDetailId ||
                !in_array($item->detail_pendaftaran_id, $scheduledDetailIds)
            )
            ->sortBy('label')
            ->values();

        // Karyawan dengan jabatan trainer
        $trainerList = Cache::remember('master:karyawan:trainers', 86400, fn() =>
            Karyawan::where('jabatan', 'trainer')
                ->where('status_akun', 'aktif')
                ->orderBy('nama_karyawan')
                ->get()
        );

        return view('penjadwalan.edit', compact('penjadwalan', 'lokasiOptions', 'siswaList', 'trainerList'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $penjadwalan = Penjadwalan::findOrFail($id);

        // Jika status Selesai dan belum ada GBMP, upload wajib
        $gbmpRules = ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:1024']; 
        if ((int) $request->input('status_jadwal') === 1 && !$penjadwalan->upload_gbmp) {
            $gbmpRules = ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:1024'];
        }

        $validated = $request->validate([
            'detail_pendaftaran_id' => ['required', 'integer', 'exists:detail_pendaftaran,id'],
            'karyawan_id'           => ['required', 'integer', 'exists:karyawans,id'],
            'lokasi'                => ['required', Rule::in(array_keys($this->lokasiOptions))],
            'jumlah_pertemuan'      => ['required', 'integer', 'min:1', 'max:999'],
            'tgl_mulai'             => ['required', 'date_format:d/m/Y'],
            'tgl_selesai'           => ['nullable', 'date_format:d/m/Y'],
            'status_jadwal'         => ['required', 'integer', Rule::in([0, 1, 2])],
            'keterangan'            => ['nullable', 'string', 'max:255'],
            'jadwal'                => ['nullable', 'array'],
            'jadwal.*.hari'         => ['nullable', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jadwal.*.jam_mulai'    => ['nullable', 'date_format:H:i,H:i:s'],
            'nama_gbmp'             => ['nullable', 'string', 'max:255'],
            'upload_gbmp'           => $gbmpRules,
            'hapus_gbmp'            => ['nullable', 'in:0,1'],
        ], $this->validationMessages());

        $tglMulai = Carbon::createFromFormat('d/m/Y', $validated['tgl_mulai']);
        $tglSelesai = !empty($validated['tgl_selesai']) ? Carbon::createFromFormat('d/m/Y', $validated['tgl_selesai']) : null;

        if ($tglSelesai && $tglSelesai->lt($tglMulai)) {
            return redirect()->back()->withInput()
                ->withErrors(['tgl_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;

            $detail = DetailPendaftaran::findOrFail($validated['detail_pendaftaran_id']);

            $pembayaranId = $penjadwalan->pembayaran_id;
            $newDetailId = $detail->id;
            $oldDetailId = $penjadwalan->detail_pendaftaran_id;

            if ($newDetailId != $oldDetailId) {
                $pembayaran = Pembayaran::where('detail_pendaftaran_id', $detail->id)
                    ->where(function ($q) {
                        $q->where('uang_muka', '>', 0)->orWhere('pelunasan', '>', 0);
                    })
                    ->first();

                if ($pembayaran) {
                    $pembayaranId = $pembayaran->id;
                }
            } elseif ($pembayaranId) {
                $pembayaran = Pembayaran::find($pembayaranId);
            }

            // Handle GBMP upload/delete
            $gbmpUpdateData = [];
            if ($request->input('hapus_gbmp') === '1') {
                // Delete existing GBMP
                if ($penjadwalan->upload_gbmp) {
                    Storage::disk('public')->delete($penjadwalan->upload_gbmp);
                }
                $gbmpUpdateData['nama_gbmp'] = null;
                $gbmpUpdateData['upload_gbmp'] = null;
            } elseif ($request->hasFile('upload_gbmp')) {
                // Upload new GBMP (delete old if exists)
                if ($penjadwalan->upload_gbmp) {
                    Storage::disk('public')->delete($penjadwalan->upload_gbmp);
                }
                $gbmpFile  = $request->file('upload_gbmp');
                $gbmpName  = $this->buildGbmpFileName($detail, (int) $validated['karyawan_id'], $gbmpFile->getClientOriginalExtension());
                $gbmpFile->storeAs('dokumen-gbmp', $gbmpName, 'public');
                $gbmpUpdateData['nama_gbmp'] = $validated['nama_gbmp'] ?? null;
                $gbmpUpdateData['upload_gbmp'] = 'dokumen-gbmp/' . $gbmpName;
            } elseif (isset($validated['nama_gbmp'])) {
                $gbmpUpdateData['nama_gbmp'] = $validated['nama_gbmp'];
            }

            $oldStatusJadwal = (int) $penjadwalan->status_jadwal;

            $penjadwalan->update(array_merge([
                'pembayaran_id'         => $pembayaranId,
                'siswa_id'              => $detail->siswa_id,
                'karyawan_id'           => $validated['karyawan_id'],
                'detail_pendaftaran_id' => $detail->id,
                'bidang_studi_id'       => $detail->bidang_studi_id,
                'level_kelas_id'        => $detail->level_kelas_id,
                'lokasi'                => $validated['lokasi'],
                'jumlah_pertemuan'      => $validated['jumlah_pertemuan'],
                'tgl_mulai'             => $tglMulai->toDateString(),
                'tgl_selesai'           => $tglSelesai ? $tglSelesai->toDateString() : null,
                'updated_by'            => $userName,
                'status_jadwal'         => $validated['status_jadwal'],
                'keterangan'            => $validated['status_jadwal'] == 2 ? ($validated['keterangan'] ?? null) : null,
            ], $gbmpUpdateData));

            // Auto-create sertifikat jika status_jadwal berubah menjadi Selesai (1)
            if ((int) $validated['status_jadwal'] === 1 && $oldStatusJadwal !== 1) {
                $existingSertifikat = Sertifikat::where('penjadwalan_id', $penjadwalan->id)->first();
                if (!$existingSertifikat) {
                    $penjadwalan->loadMissing(['siswa', 'bidangStudi', 'levelKelas']);
                    $namaBidangStudi = $penjadwalan->bidangStudi->nama_bidang_studi ?? '';
                    $noSertifikat = $this->generateNoSertifikat($namaBidangStudi);

                    Sertifikat::create([
                        'no_sertifikat'     => $noSertifikat,
                        'tanggal'           => now()->toDateString(),
                        'nama_siswa'        => $penjadwalan->siswa->nama_siswa ?? '',
                        'nama_bidang_studi' => $namaBidangStudi,
                        'level'             => $penjadwalan->levelKelas->nama_level ?? '',
                        'tgl_mulai'         => $penjadwalan->tgl_mulai ? $penjadwalan->tgl_mulai->toDateString() : null,
                        'tgl_selesai'       => $tglSelesai ? $tglSelesai->toDateString() : null,
                        'penjadwalan_id'    => $penjadwalan->id,
                        'siswa_id'          => $penjadwalan->siswa_id,
                        'signature_by'      => null,
                        'signature_date'    => null,
                        'status'            => 'pending',
                    ]);
                }
            }

            // Update detail jadwal kursus - hapus yang lama dan buat ulang
            $penjadwalan->detailPenjadwalan()->delete();
            if (!empty($validated['jadwal'])) {
                foreach ($validated['jadwal'] as $jadwal) {
                    if (!empty($jadwal['hari']) && !empty($jadwal['jam_mulai'])) {
                        DetailPenjadwalan::create([
                            'penjadwalan_id' => $penjadwalan->id,
                            'hari'           => $jadwal['hari'],
                            'jam_mulai'      => $jadwal['jam_mulai'],
                        ]);
                    }
                }
            }

            DB::commit();
            Cache::forget('penjadwalan:scheduled_detail_ids');
            Cache::forget('form:penjadwalan:siswa_base');
            return redirect()->route('penjadwalan.index')
                ->with('success', 'Data penjadwalan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Penjadwalan update error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data penjadwalan.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $penjadwalan = Penjadwalan::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($penjadwalan->upload_gbmp) {
                Storage::disk('public')->delete($penjadwalan->upload_gbmp);
            }
            $penjadwalan->delete();
            DB::commit();
            Cache::forget('penjadwalan:scheduled_detail_ids');
            Cache::forget('form:penjadwalan:siswa_base');
            return redirect()->route('penjadwalan.index')
                ->with('success', 'Data penjadwalan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data penjadwalan.');
        }
    }

    /**
     * Build the full base Pembayaran list for the penjadwalan form (no scheduling filter).
     * Cached separately so the expensive query + map is shared between create() and edit().
     * Returns a plain array of stdClass objects (serialisable by the file cache driver).
     */
    private function buildSiswaBaseList(): array
    {
        return Pembayaran::with(['siswa', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas'])
            ->where('uang_muka', '>', 0)
            ->orWhere('pelunasan', '>', 0)
            ->whereHas('siswa')
            ->whereHas('detailPendaftaran')
            ->get()
            ->map(function ($pembayaran) {
                $detail = $pembayaran->detailPendaftaran;
                if (!$detail) {
                    return null;
                }
                $namaSiswa   = $pembayaran->siswa->nama_siswa ?? '-';
                $bidangStudi = $detail->bidangStudi->nama_bidang_studi ?? '-';
                $levelKelas  = $detail->levelKelas->nama_level ?? '-';
                return (object) [
                    'pembayaran_id'         => $pembayaran->id,
                    'detail_pendaftaran_id' => $detail->id,
                    'siswa_id'              => $detail->siswa_id,
                    'bidang_studi_id'       => $detail->bidang_studi_id,
                    'level_kelas_id'        => $detail->level_kelas_id,
                    'label'                 => $namaSiswa . ' - ' . $bidangStudi . ' - ' . $levelKelas,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function buildGbmpFileName(DetailPendaftaran $detail, int $karyawanId, string $extension): string
    {
        $detail->loadMissing(['siswa', 'bidangStudi', 'levelKelas']);

        $namaSiswa   = $detail->siswa->nama_siswa ?? 'Siswa';
        $bidangStudi = $detail->bidangStudi->nama_bidang_studi ?? 'BidangStudi';
        $levelKelas  = $detail->levelKelas->nama_level ?? 'Level';

        $sanitize = fn(string $s): string => trim(str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $s));

        return implode(' - ', [
            $sanitize($namaSiswa),
            $sanitize($bidangStudi),
            $sanitize($levelKelas),
        ]) . '.' . strtolower($extension);
    }

    private function generateNoSertifikat(string $namaBidangStudi): string
    {
        $singkatan = $this->getSingkatanBidangStudi($namaBidangStudi);
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $bulanRomawi = $romawi[now()->month] ?? '';
        $tahun = now()->year;

        $lastSertifikat = Sertifikat::orderByDesc('id')->first();
        $lastNumber = 0;
        if ($lastSertifikat && preg_match('/^(\d+)\//', $lastSertifikat->no_sertifikat, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return sprintf('%04d/CM/LKP/%s/%s/%s', $lastNumber + 1, $singkatan, $bulanRomawi, $tahun);
    }

    private function getSingkatanBidangStudi(string $nama): string
    {
        $map = [
            'administrasi perkantoran' => 'AP',
            'desain grafis'            => 'DG',
            'komputer akuntansi'       => 'KA',
            'teknisi komputer'         => 'TK',
            'web programming'          => 'WP',
            'microsoft office'         => 'MO',
            'autocad'                  => 'AC',
            'digital marketing'        => 'DM',
        ];

        $lower = strtolower(trim($nama));
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        $words = explode(' ', $nama);
        $singkatan = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $singkatan .= strtoupper($word[0]);
            }
        }

        return $singkatan ?: 'XX';
    }

    private function validationMessages(): array
    {
        return [
            'detail_pendaftaran_id.required' => 'Siswa / kursus wajib dipilih.',
            'detail_pendaftaran_id.integer'  => 'Pilihan siswa tidak valid.',
            'detail_pendaftaran_id.exists'   => 'Pendaftaran kursus tidak ditemukan di database.',
            'karyawan_id.required'      => 'Trainer wajib dipilih.',
            'karyawan_id.integer'       => 'Trainer tidak valid.',
            'karyawan_id.exists'        => 'Trainer tidak ditemukan di database.',
            'lokasi.required'           => 'Lokasi wajib dipilih.',
            'lokasi.in'                 => 'Lokasi yang dipilih tidak valid.',
            'jumlah_pertemuan.required'  => 'Jumlah pertemuan wajib diisi.',
            'jumlah_pertemuan.integer'   => 'Jumlah pertemuan harus berupa angka.',
            'jumlah_pertemuan.min'       => 'Jumlah pertemuan minimal 1.',
            'jumlah_pertemuan.max'       => 'Jumlah pertemuan maksimal 999.',
            'tgl_mulai.required'         => 'Tanggal mulai wajib diisi.',
            'tgl_mulai.date_format'      => 'Format tanggal mulai harus dd/mm/yyyy (contoh: 20/10/2000).',
            'tgl_selesai.required'       => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.date_format'    => 'Format tanggal selesai harus dd/mm/yyyy (contoh: 20/10/2000).',
            'jadwal.*.hari.in'           => 'Hari yang dipilih tidak valid.',
            'jadwal.*.jam_mulai.date_format' => 'Format jam mulai tidak valid (contoh: 08:00).',
            'nama_gbmp.max'              => 'Nama GBMP maksimal 255 karakter.',
            'upload_gbmp.required'       => 'Upload GBMP wajib diisi.',
            'upload_gbmp.file'           => 'Upload GBMP harus berupa file.',
            'upload_gbmp.mimes'          => 'Format file GBMP harus PDF, JPG, atau JPEG.',
            'upload_gbmp.max'            => 'Ukuran file GBMP maksimal 1MB.',
        ];
    }
}
