<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Pelatihan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PelatihanController extends Controller
{
    private array $tempatDaftarOptions = [
        'nginden'  => 'Nginden',
        'tubanan'  => 'Tubanan',
    ];

    public function index(): View
    {
        return view('pelatihan.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['tanggal_pendaftaran', 'nama_perusahaan', 'kota', 'nama_pic', 'telepon', 'tempat_daftar'];
        $orderColumn     = $sortableColumns[max(0, $orderColIndex - 1)] ?? 'tanggal_pendaftaran';

        $totalRecords = Pelatihan::count();

        $query = Pelatihan::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('kota', 'like', "%{$search}%")
                  ->orWhere('provinsi', 'like', "%{$search}%")
                  ->orWhere('nama_pic', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $pelatihans = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();
        $tempatDaftarOptions = $this->tempatDaftarOptions;

        $data = $pelatihans->map(function ($pelatihan) use (&$no, $currentUser, $tempatDaftarOptions) {
            $no++;
            $encId = IdEncryptor::encrypt($pelatihan->id);

            $tempatLabel = $tempatDaftarOptions[$pelatihan->tempat_daftar] ?? $pelatihan->tempat_daftar;
            $tempatBadge = '<span class="px-8 py-2 rounded-pill fw-medium text-xs bg-primary-100 text-primary-600">' . e($tempatLabel) . '</span>';

            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('pelatihan', 'edit')) {
                $editBtn = '<a href="' . route('pelatihan.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('pelatihan', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($pelatihan->nama_perusahaan) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'                  => $no,
                'tanggal_pendaftaran' => e($pelatihan->tanggal_pendaftaran->format('d/m/Y')),
                'nama_perusahaan'     => e($pelatihan->nama_perusahaan),
                'kota'                => e($pelatihan->kota),
                'nama_pic'            => e($pelatihan->nama_pic),
                'telepon'             => e($pelatihan->telepon),
                'tempat_daftar'       => $tempatBadge,
                'aksi'                => '<div class="d-inline-flex align-items-center gap-8">' . $editBtn . $deleteBtn . '</div>',
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
        $tempatDaftarOptions = $this->tempatDaftarOptions;
        return view('pelatihan.create', compact('tempatDaftarOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pendaftaran' => ['required', 'date_format:d/m/Y'],
            'nama_perusahaan'     => ['required', 'string', 'max:255'],
            'alamat'              => ['required', 'string', 'max:255'],
            'kota'                => ['required', 'string', 'max:255'],
            'provinsi'            => ['required', 'string', 'max:255'],
            'nama_pic'            => ['required', 'string', 'max:255'],
            'telepon'             => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'tempat_daftar'       => ['required', Rule::in(array_keys($this->tempatDaftarOptions))],
        ], $this->validationMessages());

        DB::beginTransaction();
        try {
            Pelatihan::create([
                'tanggal_pendaftaran' => Carbon::createFromFormat('d/m/Y', $validated['tanggal_pendaftaran'])->toDateString(),
                'nama_perusahaan'     => $validated['nama_perusahaan'],
                'alamat'              => $validated['alamat'],
                'kota'                => $validated['kota'],
                'provinsi'            => $validated['provinsi'],
                'nama_pic'            => $validated['nama_pic'],
                'telepon'             => $validated['telepon'],
                'email'               => $validated['email'],
                'tempat_daftar'       => $validated['tempat_daftar'],
            ]);

            DB::commit();
            return redirect()->route('pelatihan.index')
                ->with('success', 'Data pelatihan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data pelatihan.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pelatihan = Pelatihan::findOrFail($id);
        $tempatDaftarOptions = $this->tempatDaftarOptions;
        return view('pelatihan.edit', compact('pelatihan', 'tempatDaftarOptions'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pelatihan = Pelatihan::findOrFail($id);

        $validated = $request->validate([
            'tanggal_pendaftaran' => ['required', 'date_format:d/m/Y'],
            'nama_perusahaan'     => ['required', 'string', 'max:255'],
            'alamat'              => ['required', 'string', 'max:255'],
            'kota'                => ['required', 'string', 'max:255'],
            'provinsi'            => ['required', 'string', 'max:255'],
            'nama_pic'            => ['required', 'string', 'max:255'],
            'telepon'             => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'tempat_daftar'       => ['required', Rule::in(array_keys($this->tempatDaftarOptions))],
        ], $this->validationMessages());

        DB::beginTransaction();
        try {
            $pelatihan->update([
                'tanggal_pendaftaran' => Carbon::createFromFormat('d/m/Y', $validated['tanggal_pendaftaran'])->toDateString(),
                'nama_perusahaan'     => $validated['nama_perusahaan'],
                'alamat'              => $validated['alamat'],
                'kota'                => $validated['kota'],
                'provinsi'            => $validated['provinsi'],
                'nama_pic'            => $validated['nama_pic'],
                'telepon'             => $validated['telepon'],
                'email'               => $validated['email'],
                'tempat_daftar'       => $validated['tempat_daftar'],
            ]);

            DB::commit();
            return redirect()->route('pelatihan.index')
                ->with('success', 'Data pelatihan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data pelatihan.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pelatihan = Pelatihan::findOrFail($id);

        DB::beginTransaction();
        try {
            $pelatihan->delete();
            DB::commit();
            return redirect()->route('pelatihan.index')
                ->with('success', 'Data pelatihan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data pelatihan.');
        }
    }

    private function validationMessages(): array
    {
        return [
            'tanggal_pendaftaran.required'    => 'Tanggal pendaftaran wajib diisi.',
            'tanggal_pendaftaran.date_format'  => 'Format tanggal pendaftaran harus dd/mm/yyyy (contoh: 20/10/2000).',
            'nama_perusahaan.required'         => 'Nama perusahaan wajib diisi.',
            'nama_perusahaan.max'              => 'Nama perusahaan maksimal 255 karakter.',
            'alamat.required'                  => 'Alamat wajib diisi.',
            'alamat.max'                       => 'Alamat maksimal 255 karakter.',
            'kota.required'                    => 'Kota wajib diisi.',
            'kota.max'                         => 'Kota maksimal 255 karakter.',
            'provinsi.required'                => 'Provinsi wajib diisi.',
            'provinsi.max'                     => 'Provinsi maksimal 255 karakter.',
            'nama_pic.required'                => 'Nama PIC wajib diisi.',
            'nama_pic.max'                     => 'Nama PIC maksimal 255 karakter.',
            'telepon.required'                 => 'Nomor telepon wajib diisi.',
            'telepon.max'                      => 'Nomor telepon maksimal 255 karakter.',
            'email.required'                   => 'Email wajib diisi.',
            'email.email'                      => 'Format email tidak valid.',
            'email.max'                        => 'Email maksimal 255 karakter.',
            'tempat_daftar.required'           => 'Tempat daftar wajib dipilih.',
            'tempat_daftar.in'                 => 'Tempat daftar tidak valid.',
        ];
    }
}
