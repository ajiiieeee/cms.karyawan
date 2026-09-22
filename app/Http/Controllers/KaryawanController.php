<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Exports\KaryawanMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    private array $jabatanOptions = [
        'cso' => 'CSO',
        'admin' => 'Admin',
        'manager' => 'Manager',
        'programmer' => 'Programmer',
        'desaingrafis' => 'Desain Grafis',
        'hrd' => 'HRD',
        'direktur' => 'Direktur',
        'trainer' => 'Trainer',
        'bd' => 'BD',
        'bc' => 'BC',
        'itsupport' => 'IT Support',
        'contentcreator' => 'Content Creator',
    ];

    public function index(): View
    {
        $jabatanOptions = $this->jabatanOptions;
        return view('karyawan.index', compact('jabatanOptions'));
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['nama_karyawan', 'tanggal_lahir', 'jabatan', 'telefon', 'status_akun', 'created_at'];
        $orderColumn     = $sortableColumns[max(0, $orderColIndex - 1)] ?? 'nama_karyawan';

        $totalRecords = Karyawan::count();

        $query = Karyawan::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_karyawan', 'like', "%{$search}%")
                    ->orWhere('tanggal_lahir', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('telefon', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $karyawans = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();
        $jabatanOptions = $this->jabatanOptions;

        $data = $karyawans->map(function ($karyawan) use (&$no, $currentUser, $jabatanOptions) {
            $no++;
            $encId = IdEncryptor::encrypt($karyawan->id);

            $statusBadge = $karyawan->status_akun === 'aktif'
                ? '<span class="px-6 py-2 d-flex align-items-center justify-content-center text-center rounded-3 fw-medium text-sm bg-success-100 text-success-600">Aktif</span>'
                : '<span class="px-6 py-2 d-flex align-items-center justify-content-center text-center rounded-3 fw-medium text-sm bg-warning-100 text-warning-600">Tidak Aktif</span>';

            $jabatanLabel = $jabatanOptions[$karyawan->jabatan] ?? $karyawan->jabatan;
            $jabatanBadge = '<span class="px-6 py-2 d-flex rounded-3 text-center align-items-center justify-content-center fw-medium text-xs bg-primary-100 text-primary-600">' . e($jabatanLabel) . '</span>';

            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('karyawan', 'edit')) {
                $editBtn = '<a href="' . route('karyawan.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('karyawan', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($karyawan->nama_karyawan) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'       => $no,
                'nama_karyawan' => e($karyawan->nama_karyawan),
                'tanggal_lahir' => e($karyawan->tanggal_lahir->format('d/m/Y')),
                'jabatan'  => $jabatanBadge,
                'telefon'  => e($karyawan->telefon),
                'status'   => $statusBadge,
                'aksi'     => '<div class="d-inline-flex align-items-center gap-8">' . $editBtn . $deleteBtn . '</div>',
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
        $jabatanOptions = $this->jabatanOptions;
        return view('karyawan.create', compact('jabatanOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_karyawan'       => ['required', 'string', 'max:100'],
            'nik'                 => ['required', 'string', 'max:18'],
            'jenis_kelamin'       => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'tanggal_lahir'       => ['required', 'date_format:d/m/Y'],
            'tempat_lahir'        => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'alamat'              => ['required', 'string', 'max:255'],
            'kota'                => ['nullable', 'string', 'max:100'],
            'provinsi'            => ['nullable', 'string', 'max:100'],
            'telefon'             => ['required', 'string', 'max:20'],
            'telefon_alternatif'  => ['nullable', 'string', 'max:20'],
            'pendidikan_terakhir' => ['required', 'string', 'max:100'],
            'lembaga_pendidikan'  => ['nullable', 'string', 'max:100'],
            'tahun_lulus'         => ['required', 'integer', 'min:1950', 'max:' . date('Y')],
            'jabatan'             => ['required', Rule::in(array_keys($this->jabatanOptions))],
            'tanggal_masuk'       => ['required', 'date_format:d/m/Y'],
            'foto'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status_akun'         => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'nama_keluarga'       => ['required', 'string', 'max:100'],
            'alamat_keluarga'     => ['nullable', 'string', 'max:100'],
            'pendidikan_keluarga' => ['nullable', 'string', 'max:100'],
            'hubungan_keluarga'   => ['required', 'string', 'max:100'],
            'telefon_keluarga'    => ['nullable', 'string', 'max:100'],
            'username'            => ['required', 'string', 'max:255', 'unique:karyawans,username'],
            'password'            => ['required', 'string', 'min:6', 'max:255'],
        ], $this->validationMessages());

        DB::beginTransaction();
        try {
            $fotoName = null;
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $fotoName = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('foto-karyawan', $fotoName, 'public');
            }

            Karyawan::create([
                'nama_karyawan'       => $validated['nama_karyawan'],
                'nik'                 => $validated['nik'],
                'jenis_kelamin'       => $validated['jenis_kelamin'],
                'tanggal_lahir'       => Carbon::createFromFormat('d/m/Y', $validated['tanggal_lahir'])->toDateString(),
                'tempat_lahir'        => $validated['tempat_lahir'],
                'email'               => $validated['email'],
                'alamat'              => $validated['alamat'],
                'kota'                => $validated['kota'],
                'provinsi'            => $validated['provinsi'],
                'telefon'             => $validated['telefon'],
                'telefon_alternatif'  => $validated['telefon_alternatif'],
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'],
                'lembaga_pendidikan'  => $validated['lembaga_pendidikan'],
                'tahun_lulus'         => $validated['tahun_lulus'],
                'jabatan'             => $validated['jabatan'],
                'tanggal_masuk'       => Carbon::createFromFormat('d/m/Y', $validated['tanggal_masuk'])->toDateString(),
                'foto'                => $fotoName,
                'status_akun'         => $validated['status_akun'],
                'nama_keluarga'       => $validated['nama_keluarga'],
                'alamat_keluarga'     => $validated['alamat_keluarga'],
                'pendidikan_keluarga' => $validated['pendidikan_keluarga'],
                'hubungan_keluarga'   => $validated['hubungan_keluarga'],
                'telefon_keluarga'    => $validated['telefon_keluarga'],
                'username'            => strtolower(trim($validated['username'])),
                'password'            => $validated['password'],
            ]);

            DB::commit();
            Cache::forget('master:karyawan:trainers');
            return redirect()->route('karyawan.index')
                ->with('success', 'Data karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($fotoName) {
                Storage::disk('public')->delete('foto-karyawan/' . $fotoName);
            }
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data karyawan.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $karyawan = Karyawan::findOrFail($id);
        $jabatanOptions = $this->jabatanOptions;
        return view('karyawan.edit', compact('karyawan', 'jabatanOptions'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'nama_karyawan'       => ['required', 'string', 'max:100'],
            'nik'                 => ['required', 'string', 'max:18'],
            'jenis_kelamin'       => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'tanggal_lahir'       => ['required', 'date_format:d/m/Y'],
            'tempat_lahir'        => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'alamat'              => ['required', 'string', 'max:255'],
            'kota'                => ['nullable', 'string', 'max:100'],
            'provinsi'            => ['nullable', 'string', 'max:100'],
            'telefon'             => ['required', 'string', 'max:20'],
            'telefon_alternatif'  => ['nullable', 'string', 'max:20'],
            'pendidikan_terakhir' => ['required', 'string', 'max:100'],
            'lembaga_pendidikan'  => ['nullable', 'string', 'max:100'],
            'tahun_lulus'         => ['required', 'integer', 'min:1950', 'max:' . date('Y')],
            'jabatan'             => ['required', Rule::in(array_keys($this->jabatanOptions))],
            'tanggal_masuk'       => ['required', 'date_format:d/m/Y'],
            'foto'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status_akun'         => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'nama_keluarga'       => ['required', 'string', 'max:100'],
            'alamat_keluarga'     => ['nullable', 'string', 'max:100'],
            'pendidikan_keluarga' => ['nullable', 'string', 'max:100'],
            'hubungan_keluarga'   => ['required', 'string', 'max:100'],
            'telefon_keluarga'    => ['nullable', 'string', 'max:100'],
            'username'            => ['required', 'string', 'max:255', Rule::unique('karyawans', 'username')->ignore($karyawan->id)],
            'password'            => ['nullable', 'string', 'min:6', 'max:255'],
        ], $this->validationMessages());

        DB::beginTransaction();
        try {
            $payload = [
                'nama_karyawan'       => $validated['nama_karyawan'],
                'nik'                 => $validated['nik'],
                'jenis_kelamin'       => $validated['jenis_kelamin'],
                'tanggal_lahir'       => Carbon::createFromFormat('d/m/Y', $validated['tanggal_lahir'])->toDateString(),
                'tempat_lahir'        => $validated['tempat_lahir'],
                'email'               => $validated['email'],
                'alamat'              => $validated['alamat'],
                'kota'                => $validated['kota'],
                'provinsi'            => $validated['provinsi'],
                'telefon'             => $validated['telefon'],
                'telefon_alternatif'  => $validated['telefon_alternatif'],
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'],
                'lembaga_pendidikan'  => $validated['lembaga_pendidikan'],
                'tahun_lulus'         => $validated['tahun_lulus'],
                'jabatan'             => $validated['jabatan'],
                'tanggal_masuk'       => Carbon::createFromFormat('d/m/Y', $validated['tanggal_masuk'])->toDateString(),
                'status_akun'         => $validated['status_akun'],
                'nama_keluarga'       => $validated['nama_keluarga'],
                'alamat_keluarga'     => $validated['alamat_keluarga'],
                'pendidikan_keluarga' => $validated['pendidikan_keluarga'],
                'hubungan_keluarga'   => $validated['hubungan_keluarga'],
                'telefon_keluarga'    => $validated['telefon_keluarga'],
                'username'            => strtolower(trim($validated['username'])),
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = $validated['password'];
            }

            if ($request->hasFile('foto')) {
                // Delete old photo
                if ($karyawan->foto) {
                    Storage::disk('public')->delete('foto-karyawan/' . $karyawan->foto);
                }
                $foto = $request->file('foto');
                $fotoName = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('foto-karyawan', $fotoName, 'public');
                $payload['foto'] = $fotoName;
            }

            $karyawan->update($payload);

            DB::commit();
            Cache::forget('master:karyawan:trainers');
            return redirect()->route('karyawan.index')
                ->with('success', 'Data karyawan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data karyawan.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $karyawan = Karyawan::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($karyawan->foto) {
                Storage::disk('public')->delete('foto-karyawan/' . $karyawan->foto);
            }
            $karyawan->delete();
            DB::commit();
            Cache::forget('master:karyawan:trainers');
            return redirect()->route('karyawan.index')
                ->with('success', 'Data karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data karyawan.');
        }
    }

    private function validationMessages(): array
    {
        return [
            'nama_karyawan.required'       => 'Nama karyawan wajib diisi.',
            'nama_karyawan.max'            => 'Nama karyawan maksimal 100 karakter.',
            'nik.required'                 => 'NIK wajib diisi.',
            'nik.max'                      => 'NIK maksimal 18 karakter.',
            'jenis_kelamin.required'       => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in'             => 'Jenis kelamin tidak valid.',
            'tanggal_lahir.required'       => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date_format'    => 'Format tanggal lahir harus dd/mm/yyyy (contoh: 20/10/2000).',
            'tempat_lahir.required'        => 'Tempat lahir wajib diisi.',
            'email.required'               => 'Email wajib diisi.',
            'email.email'                  => 'Format email tidak valid.',
            'email.max'                    => 'Email maksimal 255 karakter.',
            'alamat.required'              => 'Alamat wajib diisi.',
            'alamat.max'                   => 'Alamat maksimal 255 karakter.',
            'kota.max'                     => 'Kota maksimal 100 karakter.',
            'provinsi.max'                 => 'Provinsi maksimal 100 karakter.',
            'telefon.required'             => 'Nomor telepon wajib diisi.',
            'telefon.max'                  => 'Nomor telepon maksimal 20 karakter.',
            'telefon_alternatif.max'       => 'Nomor telepon alternatif maksimal 20 karakter.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib diisi.',
            'pendidikan_terakhir.max'      => 'Pendidikan terakhir maksimal 100 karakter.',
            'lembaga_pendidikan.max'       => 'Lembaga pendidikan maksimal 100 karakter.',
            'tahun_lulus.required'         => 'Tahun lulus wajib diisi.',
            'tahun_lulus.integer'          => 'Tahun lulus harus berupa angka.',
            'tahun_lulus.min'              => 'Tahun lulus minimal 1950.',
            'tahun_lulus.max'              => 'Tahun lulus maksimal ' . date('Y') . '.',
            'jabatan.required'             => 'Jabatan wajib dipilih.',
            'jabatan.in'                   => 'Jabatan tidak valid.',
            'tanggal_masuk.required'       => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date_format'    => 'Format tanggal masuk harus dd/mm/yyyy (contoh: 20/10/2000).',
            'foto.image'                   => 'File harus berupa gambar.',
            'foto.mimes'                   => 'Format gambar harus: jpg, jpeg, png, atau webp.',
            'foto.max'                     => 'Ukuran foto maksimal 2MB.',
            'status_akun.required'         => 'Status akun wajib dipilih.',
            'status_akun.in'               => 'Status akun tidak valid.',
            'nama_keluarga.required'       => 'Nama keluarga wajib diisi.',
            'nama_keluarga.max'            => 'Nama keluarga maksimal 100 karakter.',
            'alamat_keluarga.max'          => 'Alamat keluarga maksimal 100 karakter.',
            'pendidikan_keluarga.max'      => 'Pendidikan keluarga maksimal 100 karakter.',
            'hubungan_keluarga.required'   => 'Hubungan keluarga wajib diisi.',
            'hubungan_keluarga.max'        => 'Hubungan keluarga maksimal 100 karakter.',
            'telefon_keluarga.max'         => 'Telepon keluarga maksimal 100 karakter.',
            'username.required'            => 'Username wajib diisi.',
            'username.max'                 => 'Username maksimal 255 karakter.',
            'username.unique'              => 'Username sudah digunakan.',
            'password.required'            => 'Password wajib diisi.',
            'password.min'                 => 'Password minimal 6 karakter.',
        ];
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status_akun', 'jabatan']);
        $fileName = 'Laporan_Kepegawaian_HR_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new KaryawanMultiSheetExport($filters), $fileName);
    }
}
