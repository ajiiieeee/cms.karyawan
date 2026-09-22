<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Karyawan;
use App\Models\KategoriCuti;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengajuanCutiController extends Controller
{
    private array $approvalLevels = [
        0 => 'Belum Diproses',
        1 => 'HRD',
        2 => 'Manager',
        3 => 'Direktur',
    ];

    public function index(): View
    {
        return view('pengajuan-cuti.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['created_at', 'created_at', 'created_at', 'tanggal_awal', 'jumlah_hari', 'status_approval', 'status_pengajuan'];
        $orderColumn     = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = PengajuanCuti::count();

        $query = PengajuanCuti::with(['karyawan', 'kategoriCuti']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', function ($k) use ($search) {
                    $k->where('nama_karyawan', 'like', "%{$search}%");
                })
                ->orWhereHas('kategoriCuti', function ($k) use ($search) {
                    $k->where('nama_kategori', 'like', "%{$search}%");
                })
                ->orWhere('status_pengajuan', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $items = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();
        $no = $start;
        $currentUser = auth()->user();
        $approvalLevels = $this->approvalLevels;
        $isSuperAdmin = $currentUser->grup && strtolower($currentUser->grup->nama_grup) === 'super admin';

        $data = $items->map(function ($item) use (&$no, $currentUser, $approvalLevels, $isSuperAdmin) {
            $no++;
            $encId = IdEncryptor::encrypt($item->id);

            // Status pengajuan badge
            $statusBadge = match ($item->status_pengajuan) {
                'Approve' => '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-success-100 text-success-600">Approved</span>',
                'Reject'  => '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-danger-100 text-danger-600">Rejected</span>',
                default   => '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-warning-100 text-warning-600">Pending</span>',
            };

            // Determine user role from grup name (primary) or karyawan jabatan (fallback)
            $userRole = null;
            if ($currentUser->grup) {
                $grupName = strtolower($currentUser->grup->nama_grup);
                if (in_array($grupName, ['hrd', 'manager', 'direktur'])) {
                    $userRole = $grupName;
                }
            }
            if (!$userRole) {
                $karyawanMatch = Karyawan::where('username', $currentUser->username)->first();
                if ($karyawanMatch) {
                    $userRole = $karyawanMatch->jabatan;
                }
            }

            // Determine which level this user can approve
            $canApprove = false;
            if ($item->status_pengajuan === 'Pending') {
                if ($isSuperAdmin) {
                    $canApprove = true;
                } elseif ($userRole === 'hrd' && $item->status_approval === 0) {
                    $canApprove = true;
                } elseif ($userRole === 'manager' && $item->status_approval === 1) {
                    $canApprove = true;
                } elseif ($userRole === 'direktur' && $item->status_approval === 2) {
                    $canApprove = true;
                }
            }

            // Build action buttons
            $showBtn = '';
            $editBtn = '';
            $deleteBtn = '';
            $approveBtn = '';

            // Show button - always visible if user has view access
            if ($currentUser->hasMenuAccess('pengajuan-cuti', 'view')) {
                $showBtn = '<a href="' . route('pengajuan-cuti.show', $encId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Detail</a>';
            }

            if ($isSuperAdmin) {
                // Super Admin: full CRUD + approve/reject at any level
                if ($currentUser->hasMenuAccess('pengajuan-cuti', 'edit')) {
                    $editBtn = '<a href="' . route('pengajuan-cuti.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
                }
                if ($currentUser->hasMenuAccess('pengajuan-cuti', 'delete')) {
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
                }
                if ($item->status_pengajuan === 'Pending') {
                    $approveBtn = '<button type="button" class="btn btn-sm btn-outline-success-600 d-inline-flex align-items-center gap-1 btn-approve" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '" data-level="' . $item->status_approval . '"><i class="ri-checkbox-circle-line text-sm"></i> Approve</button>'
                        . '<button type="button" class="btn btn-sm btn-outline-warning-600 d-inline-flex align-items-center gap-1 btn-reject" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-close-circle-line text-sm"></i> Reject</button>';
                }
            } else {
                // HRD/Manager/Direktur: show approve/reject only when it's their turn
                if ($canApprove) {
                    $approveBtn = '<button type="button" class="btn btn-sm btn-outline-success-600 d-inline-flex align-items-center gap-1 btn-approve" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '" data-level="' . $item->status_approval . '"><i class="ri-checkbox-circle-line text-sm"></i> Approve</button>'
                        . '<button type="button" class="btn btn-sm btn-outline-warning-600 d-inline-flex align-items-center gap-1 btn-reject" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-close-circle-line text-sm"></i> Reject</button>';
                }
            }

            $adminActions = '';
            if($isSuperAdmin) {
                $adminActions = '<div class="d-flex flex-wrap align-items-center gap-2">';
            } else {
                $adminActions = '<div class="d-inline-flex align-items-center gap-2">';
            }

            return [
                'no'              => $no,
                'nama_karyawan'   => e($item->karyawan->nama_karyawan ?? '-'),
                'jenis_cuti'      => e($item->kategoriCuti->nama_kategori ?? '-'),
                'tanggal'         => e($item->tanggal_awal->format('d/m/Y')) . ' - ' . e($item->tanggal_akhir->format('d/m/Y')),
                'jumlah_hari'     => e($item->jumlah_hari) . ' hari',
                'status'          => $statusBadge,
                'aksi'            => $adminActions . $showBtn . $editBtn . $approveBtn . $deleteBtn . '</div>',
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

        $pengajuanCuti = PengajuanCuti::with(['karyawan', 'kategoriCuti'])->findOrFail($id);
        $approvalLevels = $this->approvalLevels;

        return view('pengajuan-cuti.show', compact('pengajuanCuti', 'approvalLevels'));
    }

    public function create(): View
    {
        $karyawans = Karyawan::where('status_akun', 'aktif')->orderBy('nama_karyawan')->get();
        $kategoriCutis = KategoriCuti::orderBy('nama_kategori')->get();

        return view('pengajuan-cuti.create', compact('karyawans', 'kategoriCutis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id'    => ['required', 'exists:karyawans,id'],
            'tanggal_awal'   => ['required', 'date_format:d/m/Y'],
            'tanggal_akhir'  => ['required', 'date_format:d/m/Y'],
            'jenis_cuti'     => ['required', 'exists:kategori_cuti,id'],
            'keterangan'     => ['nullable', 'string', 'max:1000'],
        ], $this->validationMessages());

        $tanggalAwal = Carbon::createFromFormat('d/m/Y', $validated['tanggal_awal']);
        $tanggalAkhir = Carbon::createFromFormat('d/m/Y', $validated['tanggal_akhir']);

        if ($tanggalAkhir->lt($tanggalAwal)) {
            return redirect()->back()->withInput()
                ->withErrors(['tanggal_akhir' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.']);
        }

        $jumlahHari = $tanggalAwal->diffInDays($tanggalAkhir) + 1;

        // Server-side saldo validation for CUTI TAHUNAN
        $kategoriCek = KategoriCuti::find($validated['jenis_cuti']);
        if ($kategoriCek && stripos($kategoriCek->nama_kategori, 'CUTI TAHUNAN (ANNUAL LEAVE)') !== false) {
            $saldoCutiCek = SaldoCuti::where('karyawan_id', $validated['karyawan_id'])
                ->where('periode_mulai', '<=', $tanggalAwal->toDateString())
                ->where('periode_selesai', '>=', $tanggalAwal->toDateString())
                ->first();
            if ($saldoCutiCek && $jumlahHari > $saldoCutiCek->saldo_sisa) {
                return redirect()->back()->withInput()
                    ->withErrors(['jenis_cuti' => 'Saldo cuti tahunan tidak mencukupi. Sisa: ' . $saldoCutiCek->saldo_sisa . ' hari, diajukan: ' . $jumlahHari . ' hari.']);
            }
        }

        DB::beginTransaction();
        try {
            PengajuanCuti::create([
                'karyawan_id'      => $validated['karyawan_id'],
                'tanggal_awal'     => $tanggalAwal->toDateString(),
                'tanggal_akhir'    => $tanggalAkhir->toDateString(),
                'jenis_cuti'       => $validated['jenis_cuti'],
                'jumlah_hari'      => (string) $jumlahHari,
                'status_approval'  => 0,
                'keterangan'       => $validated['keterangan'],
                'status_pengajuan' => 'Pending',
                'created_date'     => Carbon::now()->toDateString(),
                'created_by'       => auth()->user()->nama,
            ]);

            DB::commit();
            return redirect()->route('pengajuan-cuti.index')
                ->with('success', 'Pengajuan cuti berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan cuti.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pengajuanCuti = PengajuanCuti::findOrFail($id);
        $karyawans = Karyawan::where('status_akun', 'aktif')->orderBy('nama_karyawan')->get();
        $kategoriCutis = KategoriCuti::orderBy('nama_kategori')->get();

        return view('pengajuan-cuti.edit', compact('pengajuanCuti', 'karyawans', 'kategoriCutis'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pengajuanCuti = PengajuanCuti::findOrFail($id);

        $validated = $request->validate([
            'karyawan_id'    => ['required', 'exists:karyawans,id'],
            'tanggal_awal'   => ['required', 'date_format:d/m/Y'],
            'tanggal_akhir'  => ['required', 'date_format:d/m/Y'],
            'jenis_cuti'     => ['required', 'exists:kategori_cuti,id'],
            'keterangan'     => ['nullable', 'string', 'max:1000'],
        ], $this->validationMessages());

        $tanggalAwal = Carbon::createFromFormat('d/m/Y', $validated['tanggal_awal']);
        $tanggalAkhir = Carbon::createFromFormat('d/m/Y', $validated['tanggal_akhir']);

        if ($tanggalAkhir->lt($tanggalAwal)) {
            return redirect()->back()->withInput()
                ->withErrors(['tanggal_akhir' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.']);
        }

        $jumlahHari = $tanggalAwal->diffInDays($tanggalAkhir) + 1;

        // Server-side saldo validation for CUTI TAHUNAN
        $kategoriCek = KategoriCuti::find($validated['jenis_cuti']);
        if ($kategoriCek && stripos($kategoriCek->nama_kategori, 'CUTI TAHUNAN (ANNUAL LEAVE)') !== false) {
            $saldoCutiCek = SaldoCuti::where('karyawan_id', $validated['karyawan_id'])
                ->where('periode_mulai', '<=', $tanggalAwal->toDateString())
                ->where('periode_selesai', '>=', $tanggalAwal->toDateString())
                ->first();
            if ($saldoCutiCek && $jumlahHari > $saldoCutiCek->saldo_sisa) {
                return redirect()->back()->withInput()
                    ->withErrors(['jenis_cuti' => 'Saldo cuti tahunan tidak mencukupi. Sisa: ' . $saldoCutiCek->saldo_sisa . ' hari, diajukan: ' . $jumlahHari . ' hari.']);
            }
        }

        DB::beginTransaction();
        try {
            $pengajuanCuti->update([
                'karyawan_id'      => $validated['karyawan_id'],
                'tanggal_awal'     => $tanggalAwal->toDateString(),
                'tanggal_akhir'    => $tanggalAkhir->toDateString(),
                'jenis_cuti'       => $validated['jenis_cuti'],
                'jumlah_hari'      => (string) $jumlahHari,
                'keterangan'       => $validated['keterangan'],
                'updated_date'     => Carbon::now()->toDateString(),
                'updated_by'       => auth()->user()->nama,
            ]);

            DB::commit();
            return redirect()->route('pengajuan-cuti.index')
                ->with('success', 'Pengajuan cuti berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui pengajuan cuti.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pengajuanCuti = PengajuanCuti::findOrFail($id);

        DB::beginTransaction();
        try {
            $pengajuanCuti->delete();
            DB::commit();
            return redirect()->route('pengajuan-cuti.index')
                ->with('success', 'Pengajuan cuti berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pengajuan cuti.');
        }
    }

    public function approve(Request $request, string $encryptedId): JsonResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $pengajuanCuti = PengajuanCuti::findOrFail($id);

        if ($pengajuanCuti->status_pengajuan !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan cuti sudah diproses sebelumnya.'], 422);
        }

        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->grup && strtolower($currentUser->grup->nama_grup) === 'super admin';

        // Determine next approval level
        $nextLevel = $pengajuanCuti->status_approval + 1;

        if (!$isSuperAdmin) {
            // Determine user role from grup name (primary) or karyawan jabatan (fallback)
            $userRole = null;
            if ($currentUser->grup) {
                $grupName = strtolower($currentUser->grup->nama_grup);
                if (in_array($grupName, ['hrd', 'manager', 'direktur'])) {
                    $userRole = $grupName;
                }
            }
            if (!$userRole) {
                $karyawanMatch = Karyawan::where('username', $currentUser->username)->first();
                if ($karyawanMatch) {
                    $userRole = $karyawanMatch->jabatan;
                }
            }

            $allowedLevel = match ($userRole) {
                'hrd' => 1,
                'manager' => 2,
                'direktur' => 3,
                default => null,
            };

            if ($allowedLevel === null || $allowedLevel !== $nextLevel) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk approve pada tahap ini.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            $payload = [
                'status_approval' => $nextLevel,
                'updated_date'    => Carbon::now()->toDateString(),
                'updated_by'      => $currentUser->nama,
            ];

            // If director approves (level 3), mark as Approved
            if ($nextLevel >= 3) {
                $payload['status_approval'] = 3;
                $payload['status_pengajuan'] = 'Approve';
            }

            $pengajuanCuti->update($payload);

            DB::commit();

            $levelLabel = $this->approvalLevels[$pengajuanCuti->status_approval] ?? 'Unknown';
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti berhasil di-approve oleh ' . $levelLabel . '.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses approval.'], 500);
        }
    }

    public function reject(Request $request, string $encryptedId): JsonResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $pengajuanCuti = PengajuanCuti::findOrFail($id);

        if ($pengajuanCuti->status_pengajuan !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan cuti sudah diproses sebelumnya.'], 422);
        }

        $validated = $request->validate([
            'reject_statement' => ['required', 'string', 'max:500'],
        ], [
            'reject_statement.required' => 'Alasan penolakan wajib diisi.',
            'reject_statement.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $pengajuanCuti->update([
                'reject_statement' => $validated['reject_statement'],
                'status_pengajuan' => 'Reject',
                'updated_date'     => Carbon::now()->toDateString(),
                'updated_by'       => auth()->user()->nama,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti telah ditolak.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses penolakan.'], 500);
        }
    }

    public function checkSaldo(Request $request): JsonResponse
    {
        $karyawanId   = $request->input('karyawan_id');
        $jenisCutiId  = $request->input('jenis_cuti');
        $tanggalAwalStr  = $request->input('tanggal_awal');   // dd/mm/yyyy
        $tanggalAkhirStr = $request->input('tanggal_akhir');  // dd/mm/yyyy

        if (!$karyawanId || !$jenisCutiId || !$tanggalAwalStr || !$tanggalAkhirStr) {
            return response()->json(['type' => 'incomplete']);
        }

        // Only applies to CUTI TAHUNAN
        $kategori = KategoriCuti::find($jenisCutiId);
        if (!$kategori || stripos($kategori->nama_kategori, 'tahunan') === false) {
            return response()->json(['type' => 'not_applicable']);
        }

        // Parse dates
        try {
            $tanggalAwal  = Carbon::createFromFormat('d/m/Y', $tanggalAwalStr);
            $tanggalAkhir = Carbon::createFromFormat('d/m/Y', $tanggalAkhirStr);
        } catch (\Exception $e) {
            return response()->json(['type' => 'incomplete']);
        }

        if ($tanggalAkhir->lt($tanggalAwal)) {
            return response()->json(['type' => 'incomplete']);
        }

        $jumlahHari = $tanggalAwal->diffInDays($tanggalAkhir) + 1;

        // Find SaldoCuti record covering tanggal_awal
        $saldoCuti = SaldoCuti::where('karyawan_id', $karyawanId)
            ->where('periode_mulai', '<=', $tanggalAwal->toDateString())
            ->where('periode_selesai', '>=', $tanggalAwal->toDateString())
            ->first();

        if (!$saldoCuti) {
            return response()->json(['type' => 'not_found']);
        }

        $saldoTerpakai = $saldoCuti->saldo_terpakai;
        $saldoSisa     = $saldoCuti->saldo_sisa;
        $exceeded      = $jumlahHari > $saldoSisa;

        return response()->json([
            'type'           => 'available',
            'total_cuti'     => $saldoCuti->total_cuti,
            'saldo_terpakai' => $saldoTerpakai,
            'saldo_sisa'     => $saldoSisa,
            'jumlah_hari'    => $jumlahHari,
            'exceeded'       => $exceeded,
            'periode'        => $saldoCuti->periode_mulai->format('d/m/Y') . ' – ' . $saldoCuti->periode_selesai->format('d/m/Y'),
        ]);
    }

    private function validationMessages(): array
    {
        return [
            'karyawan_id.required'   => 'Karyawan wajib dipilih.',
            'karyawan_id.exists'     => 'Karyawan tidak ditemukan dalam data.',
            'tanggal_awal.required'  => 'Tanggal awal cuti wajib diisi.',
            'tanggal_awal.date_format' => 'Format tanggal awal harus dd/mm/yyyy (contoh: 20/10/2000).',
            'tanggal_akhir.required' => 'Tanggal akhir cuti wajib diisi.',
            'tanggal_akhir.date_format' => 'Format tanggal akhir harus dd/mm/yyyy (contoh: 20/10/2000).',
            'jenis_cuti.required'    => 'Jenis cuti wajib dipilih.',
            'jenis_cuti.exists'      => 'Jenis cuti tidak valid.',
            'keterangan.max'         => 'Keterangan maksimal 1000 karakter.',
        ];
    }
}
