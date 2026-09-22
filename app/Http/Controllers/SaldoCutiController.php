<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Karyawan;
use App\Models\SaldoCuti;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaldoCutiController extends Controller
{
    public function index(): View
    {
        return view('saldo-cuti.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'desc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['created_at', 'created_at', 'total_cuti', 'periode_mulai', 'periode_selesai'];
        $orderColumn     = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = SaldoCuti::count();

        $query = SaldoCuti::with('karyawan');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', function ($k) use ($search) {
                    $k->where('nama_karyawan', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            });
        }

        $filteredRecords = $query->count();

        $items = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no          = $start;
        $currentUser = auth()->user();

        $data = $items->map(function ($item) use (&$no, $currentUser) {
            $no++;
            $encId = IdEncryptor::encrypt($item->id);

            $today = Carbon::today();
            $isAktif = $today->between($item->periode_mulai, $item->periode_selesai);
            $statusBadge = $isAktif
                ? '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-success-100 text-success-600">Aktif</span>'
                : '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-neutral-100 text-neutral-500">Tidak Aktif</span>';

            $terpakai = $item->saldo_terpakai;
            $sisa     = $item->saldo_sisa;

            $pct = $item->total_cuti > 0 ? round(($terpakai / $item->total_cuti) * 100) : 0;
            $barColor = $pct >= 80 ? '#ef4444' : ($pct >= 50 ? '#f59e0b' : '#22c55e');

            $saldoHtml = '<div style="min-width:140px">'
                . '<div class="d-flex justify-content-between mb-4" style="font-size:.75rem">'
                . '<span class="text-neutral-500">Terpakai: <strong>' . $terpakai . '</strong></span>'
                . '<span class="text-neutral-500">Sisa: <strong>' . $sisa . '</strong></span>'
                . '</div>'
                . '<div style="height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden">'
                . '<div style="width:' . $pct . '%;height:100%;background:' . $barColor . ';border-radius:999px;transition:width .4s"></div>'
                . '</div>'
                . '<div class="text-neutral-400 mt-4" style="font-size:.7rem">Total: ' . $item->total_cuti . ' hari</div>'
                . '</div>';

            $editBtn   = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('saldo-cuti', 'edit')) {
                $editBtn = '<a href="' . route('saldo-cuti.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }
            if ($currentUser->hasMenuAccess('saldo-cuti', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($item->karyawan->nama_karyawan ?? '-') . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'           => $no,
                'nama_karyawan' => '<div><span class="fw-semibold text-sm">' . e($item->karyawan->nama_karyawan ?? '-') . '</span>'
                    . '<div class="text-neutral-400 text-xs">' . e($item->karyawan->nik ?? '-') . '</div></div>',
                'total_cuti'   => $item->total_cuti . ' hari',
                'saldo'        => $saldoHtml,
                'periode'      => '<div class="text-sm">'
                    . e($item->periode_mulai->format('d/m/Y'))
                    . '<br><span class="text-neutral-400">s.d.</span><br>'
                    . e($item->periode_selesai->format('d/m/Y'))
                    . '</div>',
                'status'       => $statusBadge,
                'aksi'         => '<div class="d-flex align-items-center gap-2">' . $editBtn . $deleteBtn . '</div>',
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
        $karyawans = Karyawan::where('status_akun', 'aktif')->orderBy('nama_karyawan')->get();

        return view('saldo-cuti.create', compact('karyawans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id'    => ['required', 'exists:karyawans,id'],
            'total_cuti'     => ['required', 'integer', 'min:1', 'max:365'],
            'periode_mulai'  => ['required', 'date_format:d/m/Y'],
            'periode_selesai' => ['required', 'date_format:d/m/Y'],
        ], $this->validationMessages());

        $periodeMultai  = Carbon::createFromFormat('d/m/Y', $validated['periode_mulai']);
        $periodeSelesai = Carbon::createFromFormat('d/m/Y', $validated['periode_selesai']);

        if ($periodeSelesai->lte($periodeMultai)) {
            return redirect()->back()->withInput()
                ->withErrors(['periode_selesai' => 'Tanggal selesai periode harus setelah tanggal mulai.']);
        }

        // Cek overlap periode untuk karyawan yang sama
        $overlap = SaldoCuti::where('karyawan_id', $validated['karyawan_id'])
            ->where(function ($q) use ($periodeMultai, $periodeSelesai) {
                $q->whereBetween('periode_mulai', [$periodeMultai, $periodeSelesai])
                  ->orWhereBetween('periode_selesai', [$periodeMultai, $periodeSelesai])
                  ->orWhere(function ($q2) use ($periodeMultai, $periodeSelesai) {
                      $q2->where('periode_mulai', '<=', $periodeMultai)
                         ->where('periode_selesai', '>=', $periodeSelesai);
                  });
            })->exists();

        if ($overlap) {
            return redirect()->back()->withInput()
                ->withErrors(['periode_mulai' => 'Karyawan ini sudah memiliki saldo cuti pada periode yang overlapping.']);
        }

        DB::beginTransaction();
        try {
            SaldoCuti::create([
                'karyawan_id'    => $validated['karyawan_id'],
                'total_cuti'     => $validated['total_cuti'],
                'periode_mulai'  => $periodeMultai->toDateString(),
                'periode_selesai' => $periodeSelesai->toDateString(),
            ]);

            DB::commit();

            return redirect()->route('saldo-cuti.index')
                ->with('success', 'Saldo cuti berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan saldo cuti.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (! $id) {
            abort(404);
        }

        $saldoCuti = SaldoCuti::with('karyawan')->findOrFail($id);
        $karyawans = Karyawan::where('status_akun', 'aktif')->orderBy('nama_karyawan')->get();

        return view('saldo-cuti.edit', compact('saldoCuti', 'karyawans'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (! $id) {
            abort(404);
        }

        $saldoCuti = SaldoCuti::findOrFail($id);

        $validated = $request->validate([
            'karyawan_id'    => ['required', 'exists:karyawans,id'],
            'total_cuti'     => ['required', 'integer', 'min:1', 'max:365'],
            'periode_mulai'  => ['required', 'date_format:d/m/Y'],
            'periode_selesai' => ['required', 'date_format:d/m/Y'],
        ], $this->validationMessages());

        $periodeMultai  = Carbon::createFromFormat('d/m/Y', $validated['periode_mulai']);
        $periodeSelesai = Carbon::createFromFormat('d/m/Y', $validated['periode_selesai']);

        if ($periodeSelesai->lte($periodeMultai)) {
            return redirect()->back()->withInput()
                ->withErrors(['periode_selesai' => 'Tanggal selesai periode harus setelah tanggal mulai.']);
        }

        // Cek overlap periode untuk karyawan yang sama (kecuali record ini sendiri)
        $overlap = SaldoCuti::where('karyawan_id', $validated['karyawan_id'])
            ->where('id', '!=', $id)
            ->where(function ($q) use ($periodeMultai, $periodeSelesai) {
                $q->whereBetween('periode_mulai', [$periodeMultai, $periodeSelesai])
                  ->orWhereBetween('periode_selesai', [$periodeMultai, $periodeSelesai])
                  ->orWhere(function ($q2) use ($periodeMultai, $periodeSelesai) {
                      $q2->where('periode_mulai', '<=', $periodeMultai)
                         ->where('periode_selesai', '>=', $periodeSelesai);
                  });
            })->exists();

        if ($overlap) {
            return redirect()->back()->withInput()
                ->withErrors(['periode_mulai' => 'Karyawan ini sudah memiliki saldo cuti pada periode yang overlapping.']);
        }

        DB::beginTransaction();
        try {
            $saldoCuti->update([
                'karyawan_id'    => $validated['karyawan_id'],
                'total_cuti'     => $validated['total_cuti'],
                'periode_mulai'  => $periodeMultai->toDateString(),
                'periode_selesai' => $periodeSelesai->toDateString(),
            ]);

            DB::commit();

            return redirect()->route('saldo-cuti.index')
                ->with('success', 'Saldo cuti berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui saldo cuti.');
        }
    }

    public function checkOverlap(Request $request): JsonResponse
    {
        $karyawanId     = $request->input('karyawan_id');
        $periodeMulai   = $request->input('periode_mulai');
        $periodeSelesai = $request->input('periode_selesai');
        $excludeEncId   = $request->input('exclude_id');

        if (! $karyawanId || ! $periodeMulai || ! $periodeSelesai) {
            return response()->json(['overlap' => false]);
        }

        try {
            $mulai   = Carbon::createFromFormat('d/m/Y', $periodeMulai);
            $selesai = Carbon::createFromFormat('d/m/Y', $periodeSelesai);
        } catch (\Exception $e) {
            return response()->json(['overlap' => false]);
        }

        if ($selesai->lte($mulai)) {
            return response()->json(['overlap' => false]);
        }

        $query = SaldoCuti::where('karyawan_id', $karyawanId)
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('periode_mulai', [$mulai, $selesai])
                  ->orWhereBetween('periode_selesai', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('periode_mulai', '<=', $mulai)
                         ->where('periode_selesai', '>=', $selesai);
                  });
            });

        if ($excludeEncId) {
            $excludeId = IdEncryptor::decrypt($excludeEncId);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        $existing = $query->first();

        if ($existing) {
            return response()->json([
                'overlap' => true,
                'message' => 'Karyawan ini sudah memiliki saldo cuti pada periode '
                    . $existing->periode_mulai->format('d/m/Y')
                    . ' s.d. '
                    . $existing->periode_selesai->format('d/m/Y'),
            ]);
        }

        return response()->json(['overlap' => false]);
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (! $id) {
            abort(404);
        }

        $saldoCuti = SaldoCuti::findOrFail($id);

        DB::beginTransaction();
        try {
            $saldoCuti->delete();
            DB::commit();

            return redirect()->route('saldo-cuti.index')
                ->with('success', 'Saldo cuti berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus saldo cuti.');
        }
    }

    private function validationMessages(): array
    {
        return [
            'karyawan_id.required'       => 'Karyawan wajib dipilih.',
            'karyawan_id.exists'         => 'Karyawan tidak ditemukan dalam data.',
            'total_cuti.required'        => 'Jumlah hari cuti wajib diisi.',
            'total_cuti.integer'         => 'Jumlah hari cuti harus berupa angka.',
            'total_cuti.min'             => 'Jumlah hari cuti minimal 1 hari.',
            'total_cuti.max'             => 'Jumlah hari cuti maksimal 365 hari.',
            'periode_mulai.required'     => 'Tanggal mulai periode wajib diisi.',
            'periode_mulai.date_format'  => 'Format tanggal mulai harus dd/mm/yyyy (contoh: 01/01/2026).',
            'periode_selesai.required'   => 'Tanggal selesai periode wajib diisi.',
            'periode_selesai.date_format' => 'Format tanggal selesai harus dd/mm/yyyy (contoh: 31/12/2026).',
        ];
    }
}
