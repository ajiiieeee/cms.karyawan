<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Sertifikat;
use App\Models\Penjadwalan;
use App\Services\SertifikatGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SertifikatController extends Controller
{
    public function index(): View
    {
        return view('sertifikat.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['no_sertifikat', 'tanggal', 'nama_siswa', 'nama_bidang_studi', 'level', 'status'];
        // Offset -2 because DataTable now has 2 non-data columns at the front: checkbox (0) and no (1)
        $orderColumn     = $sortableColumns[max(0, $orderColIndex - 2)] ?? 'tanggal';

        $totalRecords = Sertifikat::count();

        $query = Sertifikat::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_sertifikat', 'like', "%{$search}%")
                  ->orWhere('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nama_bidang_studi', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $sertifikats = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();

        $data = $sertifikats->map(function ($sertifikat) use (&$no, $currentUser) {
            $no++;
            $encId = IdEncryptor::encrypt($sertifikat->id);

            $showBtn = '';
            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('sertifikat', 'view')) {
                $showBtn = '<a href="' . route('sertifikat.show', $encId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Detail</a>';
            }

            if ($currentUser->hasMenuAccess('sertifikat', 'edit')) {
                $editBtn = '<a href="' . route('sertifikat.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('sertifikat', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($sertifikat->nama_siswa) . ' - ' . e($sertifikat->no_sertifikat) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            $downloadBtn = '';
            if ($sertifikat->signature_date !== null && $currentUser->hasMenuAccess('sertifikat', 'view')) {
                $downloadBtn = '<a href="' . route('sertifikat.download-pdf', $encId) . '" class="btn btn-sm btn-outline-success-600 d-inline-flex align-items-center gap-1" target="_blank"><i class="ri-download-2-line text-sm"></i> PDF</a>';
            }

            $statusBadge = match($sertifikat->status) {
                'pending' => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-warning">Pending</span>',
                'selesai' => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-success">Selesai</span>',
                default   => '<span class="px-12 py-6 rounded-pill fw-medium text-sm badge bg-secondary">-</span>',
            };

            $canSign = ($sertifikat->status === 'pending')
                    && ($sertifikat->signature_date === null)
                    && $currentUser->hasMenuAccess('sertifikat', 'edit');

            $checkbox = $canSign
                ? '<input type="checkbox" class="form-check-input row-checkbox"
                       data-id="' . $encId . '"
                       data-no="' . e($sertifikat->no_sertifikat) . '">'
                : '';

            return [
                'checkbox'         => $checkbox,
                'no'               => $no,
                'no_sertifikat'    => e($sertifikat->no_sertifikat),
                'tanggal'          => e($sertifikat->tanggal->format('d/m/Y')),
                'nama_siswa'       => e($sertifikat->nama_siswa),
                'bidang_studi'     => e($sertifikat->nama_bidang_studi . ' - ' . $sertifikat->level),
                'status'           => $statusBadge,
                'aksi'             => '<div class="d-inline-flex align-items-center gap-8">' . $showBtn . $editBtn . $downloadBtn . $deleteBtn . '</div>',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data->values(),
        ]);
    }

    public function show(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::with('penjadwalan')->findOrFail($id);

        return view('sertifikat.show', compact('sertifikat'));
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::findOrFail($id);

        $existingSertifikatPenjadwalanIds = Sertifikat::where('id', '!=', $sertifikat->id)
            ->pluck('penjadwalan_id')
            ->toArray();

        $penjadwalanList = Penjadwalan::with(['siswa', 'bidangStudi', 'levelKelas'])
            ->where('status_jadwal', 1)
            ->whereNotIn('id', $existingSertifikatPenjadwalanIds)
            ->get()
            ->map(function ($jadwal) {
                $namaSiswa    = $jadwal->siswa->nama_siswa ?? '-';
                $bidangStudi  = $jadwal->bidangStudi->nama_bidang_studi ?? '-';
                $levelKelas   = $jadwal->levelKelas->nama_level ?? '-';
                return (object) [
                    'id'                => $jadwal->id,
                    'nama_siswa'        => $namaSiswa,
                    'nama_bidang_studi' => $bidangStudi,
                    'level'             => $levelKelas,
                    'tgl_mulai'         => $jadwal->tgl_mulai,
                    'tgl_selesai'       => $jadwal->tgl_selesai,
                    'label'             => $namaSiswa . ' - ' . $bidangStudi . ' - ' . $levelKelas,
                ];
            })
            ->sortBy('label')
            ->values();

        return view('sertifikat.edit', compact('sertifikat', 'penjadwalanList'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::findOrFail($id);

        $validated = $request->validate([
            'penjadwalan_id'    => ['required', 'integer', 'exists:penjadwalan,id', Rule::unique('sertifikat', 'penjadwalan_id')->ignore($sertifikat->id)],
            'tanggal'           => ['required', 'date_format:d/m/Y'],
            'no_sertifikat'     => ['required', 'string', 'max:255', Rule::unique('sertifikat', 'no_sertifikat')->ignore($sertifikat->id)],
            'nama_siswa'        => ['required', 'string', 'max:255'],
            'nama_bidang_studi' => ['required', 'string', 'max:255'],
            'level'             => ['required', 'string', 'max:255'],
            'tgl_mulai'         => ['required', 'date_format:d/m/Y'],
            'tgl_selesai'       => ['required', 'date_format:d/m/Y'],
            'status'            => ['nullable', Rule::in(['pending', 'selesai'])],
            'signature_by'      => ['nullable', 'string', 'max:255'],
        ], $this->validationMessages());

        $tanggal    = Carbon::createFromFormat('d/m/Y', $validated['tanggal']);
        $tglMulai   = Carbon::createFromFormat('d/m/Y', $validated['tgl_mulai']);
        $tglSelesai = Carbon::createFromFormat('d/m/Y', $validated['tgl_selesai']);

        if ($tglSelesai->lt($tglMulai)) {
            return redirect()->back()->withInput()
                ->withErrors(['tgl_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        DB::beginTransaction();
        try {
            $signatureBy   = $sertifikat->signature_by;
            $signatureDate = $sertifikat->signature_date;

            if ($validated['status'] === 'selesai' && $sertifikat->status !== 'selesai') {
                $signatureBy   = $validated['signature_by'] ?? (Auth::user()->nama ?? Auth::user()->username);
                $signatureDate = now();
            } elseif ($validated['status'] === 'selesai' && !empty($validated['signature_by'])) {
                $signatureBy = $validated['signature_by'];
            }

            $sertifikat->update([
                'no_sertifikat'     => $validated['no_sertifikat'],
                'tanggal'           => $tanggal->toDateString(),
                'nama_siswa'        => $validated['nama_siswa'],
                'nama_bidang_studi' => $validated['nama_bidang_studi'],
                'level'             => $validated['level'],
                'tgl_mulai'         => $tglMulai->toDateString(),
                'tgl_selesai'       => $tglSelesai->toDateString(),
                'penjadwalan_id'    => $validated['penjadwalan_id'],
                'signature_by'      => $signatureBy,
                'signature_date'    => $signatureDate,
                'status'            => $validated['status'],
            ]);

            DB::commit();
            return redirect()->route('sertifikat.index')
                ->with('success', 'Data sertifikat berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sertifikat update error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data sertifikat.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::findOrFail($id);

        DB::beginTransaction();
        try {
            Cache::forget("sertifikat:qrcode:{$sertifikat->id}");
            $sertifikat->delete();
            DB::commit();
            return redirect()->route('sertifikat.index')
                ->with('success', 'Data sertifikat berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sertifikat delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data sertifikat.');
        }
    }

    public function preview(string $encryptedId)
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::findOrFail($id);

        ini_set('memory_limit', '512M');
        set_time_limit(120);
        File::ensureDirectoryExists(storage_path('fonts'));

        $viewData = (new SertifikatGenerator())->getViewData($sertifikat);

        $pdf = Pdf::loadView('sertifikat.pdf', $viewData)
            ->setPaper('a4', 'landscape');

        return $pdf->stream('preview.pdf');
    }

    public function downloadPdf(string $encryptedId)
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::findOrFail($id);

        ini_set('memory_limit', '512M');
        set_time_limit(120);
        File::ensureDirectoryExists(storage_path('fonts'));

        $viewData = (new SertifikatGenerator())->getViewData($sertifikat);

        $pdf = Pdf::loadView('sertifikat.pdf', $viewData)
            ->setPaper('a4', 'landscape');

        $sanitize = fn(string $s): string => str_replace(' ', '_', preg_replace('/[\/\\\\:*?"<>|]/', '', trim($s)));
        $filename = 'Sertifikat_'
            . $sanitize($sertifikat->nama_siswa) . '-'
            . $sanitize($sertifikat->nama_bidang_studi) . '-'
            . $sanitize($sertifikat->level) . '.pdf';

        return $pdf->download($filename);
    }

    public function generateNumber(Request $request): JsonResponse
    {
        $penjadwalanId = (int) $request->input('penjadwalan_id');
        if (!$penjadwalanId) {
            return response()->json(['no_sertifikat' => '']);
        }

        $penjadwalan = Penjadwalan::with(['siswa', 'bidangStudi', 'levelKelas'])->find($penjadwalanId);
        if (!$penjadwalan) {
            return response()->json(['no_sertifikat' => '']);
        }

        $singkatan = $this->getSingkatanBidangStudi($penjadwalan->bidangStudi->nama_bidang_studi ?? '');
        $bulanRomawi = $this->bulanRomawi(now()->month);
        $tahun = now()->year;

        $lastSertifikat = Sertifikat::orderByDesc('id')->first();
        $lastNumber = 0;
        if ($lastSertifikat && preg_match('/^(\d+)\//', $lastSertifikat->no_sertifikat, $matches)) {
            $lastNumber = (int) $matches[1];
        }
        $nextNumber = $lastNumber + 1;

        $noSertifikat = sprintf('%04d/CM/LKP/%s/%s/%s', $nextNumber, $singkatan, $bulanRomawi, $tahun);

        return response()->json([
            'no_sertifikat'     => $noSertifikat,
            'nama_siswa'        => $penjadwalan->siswa->nama_siswa ?? '',
            'nama_bidang_studi' => $penjadwalan->bidangStudi->nama_bidang_studi ?? '',
            'level'             => $penjadwalan->levelKelas->nama_level ?? '',
            'tgl_mulai'         => $penjadwalan->tgl_mulai ? $penjadwalan->tgl_mulai->format('d/m/Y') : '',
            'tgl_selesai'       => $penjadwalan->tgl_selesai ? $penjadwalan->tgl_selesai->format('d/m/Y') : '',
        ]);
    }

    /**
     * Public verification page — accessible without login via QR code.
     */
    public function verifikasi(string $token): View
    {
        $sertifikat = Sertifikat::findByVerificationToken($token);

        return view('sertifikat.verifikasi', compact('sertifikat'));
    }

    /**
     * Sign (bubuhi tanda tangan) — update signature_by, signature_date, and status.
     */
    public function sign(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $sertifikat = Sertifikat::findOrFail($id);

        if ($sertifikat->signature_date) {
            return redirect()->back()
                ->with('error', 'Sertifikat ini sudah ditandatangani sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $penjadwalan = $sertifikat->penjadwalan;
            $siswaId = $penjadwalan->siswa_id;

            $sertifikat->update([
                'signature_by'   => Auth::user()->nama ?? Auth::user()->username,
                'signature_date' => Carbon::now('Asia/Jakarta'),
                'status'         => 'selesai',
            ]);

            $siswaId = $sertifikat->siswa_id;
            $adaJadwalBelumSelesai = \App\Models\Penjadwalan::where('siswa_id', $siswaId)
                ->where('status_jadwal', '!=', 1)
                ->exists();

            if (!$adaJadwalBelumSelesai) {
                \App\Models\Siswa::where('id', $siswaId)->update(['status_siswa' => 0]);
            }

            DB::commit();
            return redirect()->route('sertifikat.edit', $encryptedId)
                ->with('success', 'Sertifikat berhasil ditandatangani.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sertifikat sign error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menandatangani sertifikat.');
        }
    }

    /**
     * Bulk sign multiple sertifikats — synchronous, optimized (zero N+1).
     */
    public function bulkSign(Request $request): JsonResponse
    {
        // 1. Permission check
        $currentUser = auth()->user();
        if (!$currentUser->hasMenuAccess('sertifikat', 'edit')) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk fitur ini.'], 403);
        }

        // 2. Validate
        $request->validate([
            'ids'   => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'string'],
        ]);

        // 3. Decrypt all IDs
        $decryptedIds = collect($request->ids)
            ->map(fn ($enc) => IdEncryptor::decrypt($enc))
            ->filter()
            ->values()
            ->all();

        if (empty($decryptedIds)) {
            return response()->json(['message' => 'ID tidak valid.'], 422);
        }

        // 4. Single query — only eligible (pending + unsigned) records
        $sertifikats = Sertifikat::whereIn('id', $decryptedIds)
            ->where('status', 'pending')
            ->whereNull('signature_date')
            ->get();

        // 5. Identify ineligible records (already signed or not found) for failedDetails
        $failedDetails = [];
        $eligibleIds   = $sertifikats->pluck('id')->all();
        $ineligibleIds = array_diff($decryptedIds, $eligibleIds);

        if (!empty($ineligibleIds)) {
            Sertifikat::whereIn('id', $ineligibleIds)
                ->get(['id', 'no_sertifikat', 'signature_date', 'status'])
                ->each(function ($s) use (&$failedDetails) {
                    $reason = $s->signature_date
                        ? 'Sertifikat sudah ditandatangani sebelumnya.'
                        : 'Sertifikat tidak ditemukan atau status tidak valid.';
                    $failedDetails[] = ['no_sertifikat' => $s->no_sertifikat, 'error' => $reason];
                });
        }

        $jobId    = uniqid('bulk_sign_', true);
        $signedBy = Auth::user()->nama ?? Auth::user()->username;
        $success  = 0;
        $failed   = count($ineligibleIds);

        // 6. Process in chunks of 50 with per-batch transaction
        foreach ($sertifikats->chunk(50) as $batchNum => $batch) {
            $batchSuccess        = 0;
            $batchFailed         = 0;
            $batchFailedDetails  = [];
            $batchLogs           = [];
            $batchSuccessSiswaIds = [];

            DB::beginTransaction();
            try {
                foreach ($batch as $sertifikat) {
                    try {
                        $sertifikat->update([
                            'signature_by'   => $signedBy,
                            'signature_date' => Carbon::now('Asia/Jakarta'),
                            'status'         => 'selesai',
                        ]);

                        Cache::forget("sertifikat:qrcode:{$sertifikat->id}");

                        if ($sertifikat->siswa_id) {
                            $batchSuccessSiswaIds[] = $sertifikat->siswa_id;
                        }

                        $batchLogs[] = [
                            'job_id'        => $jobId,
                            'sertifikat_id' => $sertifikat->id,
                            'action'        => 'signed',
                            'error_message' => null,
                            'batch_number'  => $batchNum + 1,
                            'created_at'    => now(),
                        ];
                        $batchSuccess++;

                    } catch (\Throwable $e) {
                        $batchFailed++;
                        $batchFailedDetails[] = [
                            'no_sertifikat' => $sertifikat->no_sertifikat,
                            'error'         => 'Terjadi kesalahan saat memproses sertifikat.',
                        ];
                        $batchLogs[] = [
                            'job_id'        => $jobId,
                            'sertifikat_id' => $sertifikat->id,
                            'action'        => 'failed',
                            'error_message' => $e->getMessage(),
                            'batch_number'  => $batchNum + 1,
                            'created_at'    => now(),
                        ];
                    }
                }

                // Batch siswa status update — 2 queries instead of N
                if (!empty($batchSuccessSiswaIds)) {
                    $uniqueSiswaIds = array_unique($batchSuccessSiswaIds);

                    $siswaWithUnfinished = Penjadwalan::whereIn('siswa_id', $uniqueSiswaIds)
                        ->where('status_jadwal', '!=', 1)
                        ->pluck('siswa_id')
                        ->unique()
                        ->all();

                    $siswaAllDone = array_diff($uniqueSiswaIds, $siswaWithUnfinished);
                    if (!empty($siswaAllDone)) {
                        \App\Models\Siswa::whereIn('id', $siswaAllDone)->update(['status_siswa' => 0]);
                    }
                }

                // Bulk audit log insert — single query per batch
                if (!empty($batchLogs)) {
                    DB::table('sertifikat_bulk_sign_logs')->insert($batchLogs);
                }

                DB::commit();

                // Only update outer counters after successful commit
                $success       += $batchSuccess;
                $failed        += $batchFailed;
                $failedDetails  = array_merge($failedDetails, $batchFailedDetails);

            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error("Bulk sign batch #{$batchNum} rollback: " . $e->getMessage());
                // All records in this batch failed (even per-record successes were rolled back)
                $failed += $batch->count();
            }
        }

        return response()->json([
            'status'        => 'completed',
            'total'         => count($decryptedIds),
            'success'       => $success,
            'failed'        => $failed,
            'failedDetails' => $failedDetails,
            'completedAt'   => Carbon::now('Asia/Jakarta')->toDateTimeString(),
        ]);
    }

    public function verify($id)
    {
        $sertifikat = Sertifikat::where('public_id', $id)->firstOrFail();

        if ($sertifikat->is_revoked) {
            abort(403, 'Sertifikat tidak valid');
        }

        return view('certificate.verify', compact('sertifikat'));
    }

    private function getSingkatanBidangStudi(string $nama): string
    {
        $map = [
            'Operator Komputer'         => 'OK',
            'Digital Marketing'         => 'DM',
            'Administrasi Perkantoran'  => 'AP',
            'Komputer Akuntansi'        => 'KA',
            'Desain Grafis'             => 'DG',
            'Desain Interior'           => 'DI',
            'Desain Arsitektur'         => 'DA',
            'Editing Video Multimedia'  => 'EVM',
            'Pemrograman Web'           => 'PW',
            'Website Desain CMS'        => 'CMS',
            'Pemrograman Java Android'  => 'PJA',
            'Web Designer'              => 'WD',
            'Animasi'                   => 'AN',
            'Pemrograman Dasar'         => 'PD',
            'Fotografi'                 => 'FG',
            'Lainnya'                   => 'CSM',
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

        return $singkatan ?: 'ETC';
    }

    private function bulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romawi[$bulan] ?? '';
    }

    private function validationMessages(): array
    {
        return [
            'penjadwalan_id.required'       => 'Data penjadwalan wajib dipilih.',
            'penjadwalan_id.integer'        => 'Data penjadwalan tidak valid.',
            'penjadwalan_id.exists'         => 'Data penjadwalan tidak ditemukan di database.',
            'penjadwalan_id.unique'         => 'Sertifikat untuk penjadwalan ini sudah pernah dibuat.',
            'tanggal.required'              => 'Tanggal sertifikat wajib diisi.',
            'tanggal.date_format'           => 'Format tanggal harus dd/mm/yyyy (contoh: 20/10/2000).',
            'no_sertifikat.required'        => 'Nomor sertifikat wajib diisi.',
            'no_sertifikat.max'             => 'Nomor sertifikat maksimal 255 karakter.',
            'no_sertifikat.unique'          => 'Nomor sertifikat sudah digunakan.',
            'nama_siswa.required'           => 'Nama siswa wajib diisi.',
            'nama_siswa.max'                => 'Nama siswa maksimal 255 karakter.',
            'nama_bidang_studi.required'    => 'Nama bidang studi wajib diisi.',
            'nama_bidang_studi.max'         => 'Nama bidang studi maksimal 255 karakter.',
            'level.required'                => 'Level wajib diisi.',
            'level.max'                     => 'Level maksimal 255 karakter.',
            'tgl_mulai.required'            => 'Tanggal mulai wajib diisi.',
            'tgl_mulai.date_format'         => 'Format tanggal mulai harus dd/mm/yyyy (contoh: 20/10/2000).',
            'tgl_selesai.required'          => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.date_format'       => 'Format tanggal selesai harus dd/mm/yyyy (contoh: 20/10/2000).',
            'status.in'                     => 'Status yang dipilih tidak valid.',
            'signature_by.max'              => 'Nama penanda tangan maksimal 255 karakter.',
        ];
    }
}
