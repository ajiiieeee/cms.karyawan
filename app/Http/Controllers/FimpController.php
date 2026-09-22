<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Fimp;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FimpController extends Controller
{
    private array $approvalLevels = [
        0 => 'Belum Diproses',
        1 => 'HRD',
        2 => 'Manager',
        3 => 'Direktur',
    ];

    public function index(): View
    {
        return view('fimp.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['created_at', 'created_at', 'created_at', 'tanggal_awal', 'total_hari', 'status_pengajuan', 'status_approval'];
        $orderColumn     = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = Fimp::count();

        $query = Fimp::with(['karyawan', 'penggantiKaryawan']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', function ($k) use ($search) {
                    $k->where('nama_karyawan', 'like', "%{$search}%");
                })
                ->orWhereHas('penggantiKaryawan', function ($k) use ($search) {
                    $k->where('nama_karyawan', 'like', "%{$search}%");
                })
                ->orWhere('status_approval', 'like', "%{$search}%")
                ->orWhere('keperluan', 'like', "%{$search}%");
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

            // Status approval badge
            $statusBadge = match ($item->status_approval) {
                'Approve' => '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-success-100 text-success-600">Approved</span>',
                'Reject'  => '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-danger-100 text-danger-600">Rejected</span>',
                default   => '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-warning-100 text-warning-600">Pending</span>',
            };

            // Approval level badge
            $approvalLabel = $approvalLevels[$item->status_pengajuan] ?? 'Unknown';
            $approvalBadge = match ($item->status_pengajuan) {
                0 => '<span class="px-6 rounded-pill fw-medium text-xs bg-neutral-100 text-neutral-600">' . e($approvalLabel) . '</span>',
                1 => '<span class="px-6 rounded-pill fw-medium text-xs bg-info-100 text-info-600">' . e($approvalLabel) . '</span>',
                2 => '<span class="px-6 rounded-pill fw-medium text-xs bg-primary-100 text-primary-600">' . e($approvalLabel) . '</span>',
                3 => '<span class="px-6 rounded-pill fw-medium text-xs bg-success-100 text-success-600">' . e($approvalLabel) . '</span>',
                default => '<span class="px-6 rounded-pill fw-medium text-xs bg-neutral-100 text-neutral-600">' . e($approvalLabel) . '</span>',
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
            if ($item->status_approval === 'Pending') {
                if ($isSuperAdmin) {
                    $canApprove = true;
                } elseif ($userRole === 'hrd' && $item->status_pengajuan === 0) {
                    $canApprove = true;
                } elseif ($userRole === 'manager' && $item->status_pengajuan === 1) {
                    $canApprove = true;
                } elseif ($userRole === 'direktur' && $item->status_pengajuan === 2) {
                    $canApprove = true;
                }
            }

            // Build action buttons
            $showBtn = '';
            $editBtn = '';
            $deleteBtn = '';
            $approveBtn = '';

            if ($currentUser->hasMenuAccess('pengajuan-fimp', 'view')) {
                $showBtn = '<a href="' . route('pengajuan-fimp.show', $encId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Detail</a>';
            }

            if ($isSuperAdmin) {
                if ($currentUser->hasMenuAccess('pengajuan-fimp', 'edit')) {
                    $editBtn = '<a href="' . route('pengajuan-fimp.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
                }
                if ($currentUser->hasMenuAccess('pengajuan-fimp', 'delete')) {
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
                }
                if ($item->status_approval === 'Pending') {
                    $approveBtn = '<button type="button" class="btn btn-sm btn-outline-success-600 d-inline-flex align-items-center gap-1 btn-approve" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '" data-level="' . $item->status_pengajuan . '"><i class="ri-checkbox-circle-line text-sm"></i> Approve</button>'
                        . '<button type="button" class="btn btn-sm btn-outline-warning-600 d-inline-flex align-items-center gap-1 btn-reject" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-close-circle-line text-sm"></i> Reject</button>';
                }
            } else {
                if ($canApprove) {
                    $approveBtn = '<button type="button" class="btn btn-sm btn-outline-success-600 d-inline-flex align-items-center gap-1 btn-approve" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '" data-level="' . $item->status_pengajuan . '"><i class="ri-checkbox-circle-line text-sm"></i> Approve</button>'
                        . '<button type="button" class="btn btn-sm btn-outline-warning-600 d-inline-flex align-items-center gap-1 btn-reject" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-close-circle-line text-sm"></i> Reject</button>';
                }
            }

            return [
                'no'                => $no,
                'nama_karyawan'     => e($item->karyawan->nama_karyawan ?? '-'),
                'pengganti'         => e($item->penggantiKaryawan->nama_karyawan ?? '-'),
                'tanggal'           => e($item->tanggal_awal->format('d/m/Y')) . ' - ' . e($item->tanggal_akhir->format('d/m/Y')),
                'total_hari'        => e($item->total_hari) . ' hari',
                'approval'          => $approvalBadge,
                'status'            => $statusBadge,
                'aksi'              => '<div class="d-inline-flex align-items-center gap-8">' . $showBtn . $editBtn . $approveBtn . $deleteBtn . '</div>',
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

        $fimp = Fimp::with(['karyawan', 'penggantiKaryawan'])->findOrFail($id);
        $approvalLevels = $this->approvalLevels;

        return view('fimp.show', compact('fimp', 'approvalLevels'));
    }

    public function create(): View
    {
        $karyawans = Karyawan::where('status_akun', 'aktif')->orderBy('nama_karyawan')->get();

        return view('fimp.create', compact('karyawans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id'           => ['required', 'exists:karyawans,id'],
            'tanggal_awal'          => ['required', 'date_format:d/m/Y'],
            'tanggal_akhir'         => ['required', 'date_format:d/m/Y'],
            'karyawan_pengganti'    => ['required', 'exists:karyawans,id', 'different:karyawan_id'],
            'keperluan'             => ['required', 'string', 'max:2000'],
            'pengganti_mengetahui'  => ['required', 'accepted'],
            'pengganti_bersedia'    => ['required', 'accepted'],
        ], $this->validationMessages());

        $tanggalAwal = Carbon::createFromFormat('d/m/Y', $validated['tanggal_awal']);
        $tanggalAkhir = Carbon::createFromFormat('d/m/Y', $validated['tanggal_akhir']);

        if ($tanggalAkhir->lt($tanggalAwal)) {
            return redirect()->back()->withInput()
                ->withErrors(['tanggal_akhir' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.']);
        }

        $totalHari = $tanggalAwal->diffInDays($tanggalAkhir) + 1;

        DB::beginTransaction();
        try {
            Fimp::create([
                'karyawan_id'          => $validated['karyawan_id'],
                'tanggal_awal'         => $tanggalAwal->toDateString(),
                'tanggal_akhir'        => $tanggalAkhir->toDateString(),
                'total_hari'           => $totalHari,
                'karyawan_pengganti'   => $validated['karyawan_pengganti'],
                'keperluan'            => $validated['keperluan'],
                'pengganti_mengetahui' => true,
                'pengganti_bersedia'   => true,
                'status_pengajuan'     => 0,
                'status_approval'      => 'Pending',
                'created_date'         => Carbon::now()->toDateString(),
                'created_by'           => auth()->user()->nama,
            ]);

            DB::commit();
            return redirect()->route('pengajuan-fimp.index')
                ->with('success', 'Pengajuan FIMP berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan FIMP.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $fimp = Fimp::findOrFail($id);
        $karyawans = Karyawan::where('status_akun', 'aktif')->orderBy('nama_karyawan')->get();

        return view('fimp.edit', compact('fimp', 'karyawans'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $fimp = Fimp::findOrFail($id);

        $validated = $request->validate([
            'karyawan_id'           => ['required', 'exists:karyawans,id'],
            'tanggal_awal'          => ['required', 'date_format:d/m/Y'],
            'tanggal_akhir'         => ['required', 'date_format:d/m/Y'],
            'karyawan_pengganti'    => ['required', 'exists:karyawans,id', 'different:karyawan_id'],
            'keperluan'             => ['required', 'string', 'max:2000'],
            'pengganti_mengetahui'  => ['required', 'accepted'],
            'pengganti_bersedia'    => ['required', 'accepted'],
        ], $this->validationMessages());

        $tanggalAwal = Carbon::createFromFormat('d/m/Y', $validated['tanggal_awal']);
        $tanggalAkhir = Carbon::createFromFormat('d/m/Y', $validated['tanggal_akhir']);

        if ($tanggalAkhir->lt($tanggalAwal)) {
            return redirect()->back()->withInput()
                ->withErrors(['tanggal_akhir' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.']);
        }

        $totalHari = $tanggalAwal->diffInDays($tanggalAkhir) + 1;

        DB::beginTransaction();
        try {
            $fimp->update([
                'karyawan_id'          => $validated['karyawan_id'],
                'tanggal_awal'         => $tanggalAwal->toDateString(),
                'tanggal_akhir'        => $tanggalAkhir->toDateString(),
                'total_hari'           => $totalHari,
                'karyawan_pengganti'   => $validated['karyawan_pengganti'],
                'keperluan'            => $validated['keperluan'],
                'pengganti_mengetahui' => $request->has('pengganti_mengetahui'),
                'pengganti_bersedia'   => $request->has('pengganti_bersedia'),
                'updated_date'         => Carbon::now()->toDateString(),
                'updated_by'           => auth()->user()->nama,
            ]);

            DB::commit();
            return redirect()->route('pengajuan-fimp.index')
                ->with('success', 'Pengajuan FIMP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui pengajuan FIMP.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $fimp = Fimp::findOrFail($id);

        DB::beginTransaction();
        try {
            $fimp->delete();
            DB::commit();
            return redirect()->route('pengajuan-fimp.index')
                ->with('success', 'Pengajuan FIMP berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pengajuan FIMP.');
        }
    }

    public function approve(Request $request, string $encryptedId): JsonResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $fimp = Fimp::findOrFail($id);

        if ($fimp->status_approval !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan FIMP sudah diproses sebelumnya.'], 422);
        }

        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->grup && strtolower($currentUser->grup->nama_grup) === 'super admin';

        $nextLevel = $fimp->status_pengajuan + 1;

        if (!$isSuperAdmin) {
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
                'status_pengajuan' => $nextLevel,
                'updated_date'     => Carbon::now()->toDateString(),
                'updated_by'       => $currentUser->nama,
            ];

            if ($nextLevel >= 3) {
                $payload['status_pengajuan'] = 3;
                $payload['status_approval'] = 'Approve';
            }

            $fimp->update($payload);

            DB::commit();

            $levelLabel = $this->approvalLevels[$fimp->status_pengajuan] ?? 'Unknown';
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan FIMP berhasil di-approve oleh ' . $levelLabel . '.',
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

        $fimp = Fimp::findOrFail($id);

        if ($fimp->status_approval !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan FIMP sudah diproses sebelumnya.'], 422);
        }

        $validated = $request->validate([
            'reject_statement' => ['required', 'string', 'max:500'],
        ], [
            'reject_statement.required' => 'Alasan penolakan wajib diisi.',
            'reject_statement.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $fimp->update([
                'reject_statement' => $validated['reject_statement'],
                'status_approval'  => 'Reject',
                'updated_date'     => Carbon::now()->toDateString(),
                'updated_by'       => auth()->user()->nama,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan FIMP telah ditolak.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses penolakan.'], 500);
        }
    }

    private function validationMessages(): array
    {
        return [
            'karyawan_id.required'          => 'Karyawan wajib dipilih.',
            'karyawan_id.exists'            => 'Karyawan tidak ditemukan dalam data.',
            'tanggal_awal.required'         => 'Tanggal awal wajib diisi.',
            'tanggal_awal.date_format'      => 'Format tanggal awal harus dd/mm/yyyy (contoh: 20/10/2000).',
            'tanggal_akhir.required'        => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.date_format'     => 'Format tanggal akhir harus dd/mm/yyyy (contoh: 20/10/2000).',
            'karyawan_pengganti.required'   => 'Karyawan pengganti wajib dipilih.',
            'karyawan_pengganti.exists'     => 'Karyawan pengganti tidak ditemukan dalam data.',
            'karyawan_pengganti.different'  => 'Karyawan pengganti tidak boleh sama dengan karyawan yang mengajukan.',
            'keperluan.required'            => 'Keperluan wajib diisi.',
            'keperluan.max'                 => 'Keperluan maksimal 2000 karakter.',
            'pengganti_mengetahui.required' => 'Pernyataan pengganti mengetahui wajib dicentang.',
            'pengganti_mengetahui.accepted' => 'Pernyataan pengganti mengetahui harus dicentang.',
            'pengganti_bersedia.required'   => 'Pernyataan pengganti bersedia wajib dicentang.',
            'pengganti_bersedia.accepted'   => 'Pernyataan pengganti bersedia harus dicentang.',
        ];
    }
}
