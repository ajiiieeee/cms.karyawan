<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\DetailPendaftaran;
use App\Models\DetailPenjadwalan;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\Penjadwalan;
use App\Models\Sertifikat;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Exports\SiswaMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    private array $agamaOptions = [
        'islam' => 'Islam',
        'kristen' => 'Kristen',
        'katolik' => 'Katolik',
        'hindu' => 'Hindu',
        'buddha' => 'Buddha',
        'konghucu' => 'Konghucu',
    ];

    private array $jenisTinggalOptions = [
        'rumah_sendiri' => 'Rumah Sendiri',
        'rumah_orang_tua' => 'Rumah Orang Tua',
        'kos' => 'Kos/Kontrakan',
        'rumah_saudara' => 'Rumah Saudara',
        'asrama' => 'Asrama',
        'lainnya' => 'Lainnya',
    ];

    private array $pendidikanOptions = [
        'sd' => 'SD',
        'smp' => 'SMP',
        'sma_smk' => 'SMA/SMK',
        'd1' => 'D1',
        'd2' => 'D2',
        'd3' => 'D3',
        'd4/s1' => 'D4/S1',
        's2' => 'S2',
        's3' => 'S3',
        'lainnya' => 'Lainnya',
    ];

    private array $abkOptions = [
        'tidak_ada' => 'Tidak Ada',
        'tunanetra' => 'Tunanetra',
        'tunarungu' => 'Tunarungu',
        'tunawicara' => 'Tunawicara',
        'tunadaksa' => 'Tunadaksa',
        'tunagrahita' => 'Tunagrahita',
        'tunalaras' => 'Tunalaras',
        'autis' => 'Autis',
        'adhd' => 'ADHD',
        'lainnya' => 'Lainnya',
    ];

    private array $pekerjaanOptions = [
        'pelajar' => 'Pelajar/Mahasiswa',
        'pns' => 'PNS',
        'karyawan_swasta' => 'Karyawan Swasta',
        'wiraswasta' => 'Wiraswasta',
        'freelancer' => 'Freelancer',
        'tidak_bekerja' => 'Tidak Bekerja',
        'lainnya' => 'Lainnya',
    ];

    public function index(): View
    {
        return view('siswa.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['nama_siswa', 'nik', 'jenis_kelamin', 'no_telepon', 'status_siswa', 'created_at'];
        $orderColumn     = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = Siswa::count();

        $query = Siswa::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $siswas = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();

        $data = $siswas->map(function ($siswa) use (&$no, $currentUser) {
            $no++;
            $encId = IdEncryptor::encrypt($siswa->id);

            $statusBadge = $siswa->status_siswa
                ? '<span class="px-6 py-4 rounded-pill fw-medium text-sm bg-success-100 text-success-600">Aktif</span>'
                : '<span class="px-6 py-4 rounded-pill fw-medium text-sm bg-warning-100 text-warning-600">Tidak Aktif</span>';

            $jenisKelaminLabel = $siswa->jenis_kelamin === 'laki_laki' ? 'Laki-laki' : 'Perempuan';

            $editBtn = '';
            $showBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('siswa', 'edit')) {
                $editBtn = '<a href="' . route('siswa.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('siswa', 'view')) {
                $showBtn = '<a href="' . route('siswa.show', $encId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Lihat</a>';
            }

            if ($currentUser->hasMenuAccess('siswa', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($siswa->nama_siswa) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'             => $no,
                'nama_siswa'     => e($siswa->nama_siswa),
                'nik'            => e($siswa->nik),
                'jenis_kelamin'  => e($jenisKelaminLabel),
                'no_telepon'     => e($siswa->no_telepon),
                'status'         => $statusBadge,
                'aksi'           => '<div class="d-inline-flex align-items-center gap-8">' . $showBtn . $editBtn . $deleteBtn . '</div>',
                'created_at'     => $siswa->created_at,
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
        return view('siswa.create', $this->getFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        DB::beginTransaction();
        try {
            $fotoName = null;
            $ktpName = null;
            $kkName = null;

            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $fotoName = time() . '_foto_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('siswa/foto', $fotoName, 'public');
            }

            if ($request->hasFile('upload_ktp')) {
                $ktp = $request->file('upload_ktp');
                $ktpName = time() . '_ktp_' . uniqid() . '.' . $ktp->getClientOriginalExtension();
                $ktp->storeAs('siswa/ktp', $ktpName, 'public');
            }

            if ($request->hasFile('upload_kk')) {
                $kk = $request->file('upload_kk');
                $kkName = time() . '_kk_' . uniqid() . '.' . $kk->getClientOriginalExtension();
                $kk->storeAs('siswa/kk', $kkName, 'public');
            }

            Siswa::create([
                'nama_siswa'          => $validated['nama_siswa'],
                'nik'                 => $validated['nik'],
                'nis'                 => $validated['nis'],
                'jenis_kelamin'       => $validated['jenis_kelamin'],
                'tempat_lahir'        => $validated['tempat_lahir'],
                'tanggal_lahir'       => Carbon::createFromFormat('d/m/Y', $validated['tanggal_lahir'])->toDateString(),
                'agama'               => $validated['agama'],
                'email'               => $validated['email'],
                'no_telepon'          => $validated['no_telepon'],
                'alamat'              => $validated['alamat'],
                'jenis_tinggal'       => $validated['jenis_tinggal'],
                'jenis_tinggal_lainnya' => $validated['jenis_tinggal'] === 'lainnya' ? $validated['jenis_tinggal_lainnya'] : null,
                'kota'                => $validated['kota'],
                'provinsi'            => $validated['provinsi'],
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'],
                'pendidikan_terakhir_lainnya' => $validated['pendidikan_terakhir'] === 'lainnya' ? $validated['pendidikan_terakhir_lainnya'] : null,
                'abk'                 => $validated['abk'],
                'abk_lainnya'         => $validated['abk'] === 'lainnya' ? $validated['abk_lainnya'] : null,
                'pekerjaan'           => $validated['pekerjaan'],
                'pekerjaan_lainnya'   => $validated['pekerjaan'] === 'lainnya' ? $validated['pekerjaan_lainnya'] : null,
                'foto'                => $fotoName,
                'status_siswa'        => $validated['status_siswa'],
                'upload_ktp'          => $ktpName,
                'upload_kk'           => $kkName,
            ]);

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', 'Data siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($fotoName) {
                Storage::disk('public')->delete('siswa/foto/' . $fotoName);
            }
            if ($ktpName) {
                Storage::disk('public')->delete('siswa/ktp/' . $ktpName);
            }
            if ($kkName) {
                Storage::disk('public')->delete('siswa/kk/' . $kkName);
            }
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data siswa.');
        }
    }

    public function show(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        // Eager loading relasi pendaftaran, detail, penjadwalan, hingga sertifikat
        $siswa = Siswa::with([
            'pendaftaran.detailPendaftaran.bidangStudi',
            'pendaftaran.detailPendaftaran.levelKelas',
            'pendaftaran.detailPendaftaran.kategoriKelas',
            'pendaftaran.detailPendaftaran.penjadwalan.sertifikat',
        ])->findOrFail($id);

        // Ambil detail penjadwalan milik siswa ini
        $detailPenjadwalan = \App\Models\DetailPenjadwalan::with([
            'penjadwalan.karyawan',
            'penjadwalan.bidangStudi'
        ])
            ->whereHas('penjadwalan', function ($query) use ($id) {
                $query->where('siswa_id', $id);
            })
            ->get();

        return view('siswa.show', [
            'siswa'             => $siswa,
            'detailPenjadwalan' => $detailPenjadwalan
        ]);
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $siswa = Siswa::findOrFail($id);
        return view('siswa.edit', array_merge(['siswa' => $siswa], $this->getFormOptions()));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $siswa = Siswa::findOrFail($id);

        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        DB::beginTransaction();
        try {
            $payload = [
                'nama_siswa'          => $validated['nama_siswa'],
                'nis'                 => $validated['nis'],
                'nik'                 => $validated['nik'],
                'jenis_kelamin'       => $validated['jenis_kelamin'],
                'tempat_lahir'        => $validated['tempat_lahir'],
                'tanggal_lahir'       => Carbon::createFromFormat('d/m/Y', $validated['tanggal_lahir'])->toDateString(),
                'agama'               => $validated['agama'],
                'email'               => $validated['email'],
                'no_telepon'          => $validated['no_telepon'],
                'alamat'              => $validated['alamat'],
                'jenis_tinggal'       => $validated['jenis_tinggal'],
                'jenis_tinggal_lainnya' => $validated['jenis_tinggal'] === 'lainnya' ? $validated['jenis_tinggal_lainnya'] : null,
                'kota'                => $validated['kota'],
                'provinsi'            => $validated['provinsi'],
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'],
                'pendidikan_terakhir_lainnya' => $validated['pendidikan_terakhir'] === 'lainnya' ? $validated['pendidikan_terakhir_lainnya'] : null,
                'abk'                 => $validated['abk'],
                'abk_lainnya'         => $validated['abk'] === 'lainnya' ? $validated['abk_lainnya'] : null,
                'pekerjaan'           => $validated['pekerjaan'],
                'pekerjaan_lainnya'   => $validated['pekerjaan'] === 'lainnya' ? $validated['pekerjaan_lainnya'] : null,
                'status_siswa'        => $validated['status_siswa'],
            ];

            if ($request->hasFile('foto')) {
                if ($siswa->foto) {
                    Storage::disk('public')->delete('siswa/foto/' . $siswa->foto);
                }
                $foto = $request->file('foto');
                $fotoName = time() . '_foto_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('siswa/foto', $fotoName, 'public');
                $payload['foto'] = $fotoName;
            }

            if ($request->hasFile('upload_ktp')) {
                if ($siswa->upload_ktp) {
                    Storage::disk('public')->delete('siswa/ktp/' . $siswa->upload_ktp);
                }
                $ktp = $request->file('upload_ktp');
                $ktpName = time() . '_ktp_' . uniqid() . '.' . $ktp->getClientOriginalExtension();
                $ktp->storeAs('siswa/ktp', $ktpName, 'public');
                $payload['upload_ktp'] = $ktpName;
            }

            if ($request->hasFile('upload_kk')) {
                if ($siswa->upload_kk) {
                    Storage::disk('public')->delete('siswa/kk/' . $siswa->upload_kk);
                }
                $kk = $request->file('upload_kk');
                $kkName = time() . '_kk_' . uniqid() . '.' . $kk->getClientOriginalExtension();
                $kk->storeAs('siswa/kk', $kkName, 'public');
                $payload['upload_kk'] = $kkName;
            }

            $siswa->update($payload);

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', 'Data siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data siswa.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $siswa = Siswa::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. Hapus sertifikat terkait siswa
            Sertifikat::where('siswa_id', $id)->delete();

            // 2. Hapus detail_penjadwalan dari semua penjadwalan milik siswa ini
            $penjadwalanIds = Penjadwalan::where('siswa_id', $id)->pluck('id');
            if ($penjadwalanIds->isNotEmpty()) {
                DetailPenjadwalan::whereIn('penjadwalan_id', $penjadwalanIds)->delete();
            }

            // 3. Hapus penjadwalan beserta file upload_gbmp
            $penjadwalans = Penjadwalan::where('siswa_id', $id)->get();
            foreach ($penjadwalans as $penjadwalan) {
                if ($penjadwalan->upload_gbmp) {
                    Storage::disk('public')->delete($penjadwalan->upload_gbmp);
                }
                $penjadwalan->delete();
            }

            // 4. Hapus pembayaran beserta file bukti
            $pembayarans = Pembayaran::where('siswa_id', $id)->get();
            foreach ($pembayarans as $pembayaran) {
                if ($pembayaran->bukti_uang_muka) {
                    Storage::disk('public')->delete($pembayaran->bukti_uang_muka);
                }
                if ($pembayaran->bukti_pelunasan) {
                    Storage::disk('public')->delete($pembayaran->bukti_pelunasan);
                }
                $pembayaran->delete();
            }

            // 5. Hapus detail_pendaftaran
            DetailPendaftaran::where('siswa_id', $id)->delete();

            // 6. Hapus pendaftaran beserta file bukti_pembayaran
            $pendaftarans = Pendaftaran::where('siswa_id', $id)->get();
            foreach ($pendaftarans as $pendaftaran) {
                if ($pendaftaran->bukti_pembayaran) {
                    Storage::disk('public')->delete($pendaftaran->bukti_pembayaran);
                }
                $pendaftaran->delete();
            }

            // 7. Hapus file milik siswa
            if ($siswa->foto) {
                Storage::disk('public')->delete('siswa/foto/' . $siswa->foto);
            }
            if ($siswa->upload_ktp) {
                Storage::disk('public')->delete('siswa/ktp/' . $siswa->upload_ktp);
            }
            if ($siswa->upload_kk) {
                Storage::disk('public')->delete('siswa/kk/' . $siswa->upload_kk);
            }

            // 8. Hapus siswa
            $namaForLog = $siswa->nama_siswa;
            $siswa->delete();

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', 'Data siswa ' . $namaForLog . ' beserta semua data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('SiswaController::destroy - Error: ' . $e->getMessage(), ['siswa_id' => $id]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data siswa.');
        }
    }

    private function getFormOptions(): array
    {
        return [
            'agamaOptions'      => $this->agamaOptions,
            'jenisTinggalOptions' => $this->jenisTinggalOptions,
            'pendidikanOptions' => $this->pendidikanOptions,
            'abkOptions'        => $this->abkOptions,
            'pekerjaanOptions'  => $this->pekerjaanOptions,
        ];
    }

    private function validationRules(): array
    {
        return [
            'nama_siswa'          => ['required', 'string', 'max:255'],
            'nis'                 => ['required', 'string', 'max:18'],
            'nik'                 => ['required', 'string', 'max:20'],
            'jenis_kelamin'       => ['required', Rule::in(['laki_laki', 'perempuan'])],
            'tempat_lahir'        => ['required', 'string', 'max:255'],
            'tanggal_lahir'       => ['required', 'date_format:d/m/Y'],
            'agama'               => ['required', Rule::in(array_keys($this->agamaOptions))],
            'email'               => ['required', 'email', 'max:255'],
            'no_telepon'          => ['required', 'string', 'max:20'],
            'alamat'              => ['required', 'string', 'max:255'],
            'jenis_tinggal'       => ['required', Rule::in(array_keys($this->jenisTinggalOptions))],
            'jenis_tinggal_lainnya' => ['nullable', 'required_if:jenis_tinggal,lainnya', 'string', 'max:255'],
            'kota'                => ['required', 'string', 'max:255'],
            'provinsi'            => ['required', 'string', 'max:255'],
            'pendidikan_terakhir' => ['required', Rule::in(array_keys($this->pendidikanOptions))],
            'pendidikan_terakhir_lainnya' => ['nullable', 'required_if:pendidikan_terakhir,lainnya', 'string', 'max:255'],
            'abk'                 => ['required', Rule::in(array_keys($this->abkOptions))],
            'abk_lainnya'         => ['nullable', 'required_if:abk,lainnya', 'string', 'max:255'],
            'pekerjaan'           => ['required', Rule::in(array_keys($this->pekerjaanOptions))],
            'pekerjaan_lainnya'   => ['nullable', 'required_if:pekerjaan,lainnya', 'string', 'max:255'],
            'foto'                => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'status_siswa'        => ['required', Rule::in([0, 1])],
            'upload_ktp'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'upload_kk'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'nama_siswa.required'          => 'Nama siswa wajib diisi.',
            'nama_siswa.max'               => 'Nama siswa maksimal 255 karakter.',
            'nis.required'                 => 'NIS wajib diisi.',
            'nis.max'                      => 'NIS maksimal 18 karakter.',
            'jenis_kelamin.required'       => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in'             => 'Jenis kelamin tidak valid.',
            'tempat_lahir.required'        => 'Tempat lahir wajib diisi.',
            'tempat_lahir.max'             => 'Tempat lahir maksimal 255 karakter.',
            'tanggal_lahir.required'       => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date_format'    => 'Format tanggal lahir harus dd/mm/yyyy (contoh: 20/10/2000).',
            'agama.required'               => 'Agama wajib dipilih.',
            'agama.in'                     => 'Agama tidak valid.',
            'email.required'               => 'Email wajib diisi.',
            'email.email'                  => 'Format email tidak valid.',
            'email.max'                    => 'Email maksimal 255 karakter.',
            'no_telepon.required'          => 'Nomor telepon wajib diisi.',
            'no_telepon.max'               => 'Nomor telepon maksimal 20 karakter.',
            'alamat.required'              => 'Alamat wajib diisi.',
            'alamat.max'                   => 'Alamat maksimal 255 karakter.',
            'jenis_tinggal.required'       => 'Jenis tempat tinggal wajib dipilih.',
            'jenis_tinggal.in'             => 'Jenis tempat tinggal tidak valid.',
            'jenis_tinggal_lainnya.required_if' => 'Jenis tempat tinggal lainnya wajib diisi jika memilih Lainnya.',
            'jenis_tinggal_lainnya.max'    => 'Jenis tempat tinggal lainnya maksimal 255 karakter.',
            'kota.required'                => 'Kota wajib diisi.',
            'kota.max'                     => 'Kota maksimal 255 karakter.',
            'provinsi.required'            => 'Provinsi wajib diisi.',
            'provinsi.max'                 => 'Provinsi maksimal 255 karakter.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib dipilih.',
            'pendidikan_terakhir.in'       => 'Pendidikan terakhir tidak valid.',
            'pendidikan_terakhir_lainnya.required_if' => 'Pendidikan terakhir lainnya wajib diisi jika memilih Lainnya.',
            'pendidikan_terakhir_lainnya.max' => 'Pendidikan terakhir lainnya maksimal 255 karakter.',
            'abk.required'                 => 'Kebutuhan khusus wajib dipilih.',
            'abk.in'                       => 'Kebutuhan khusus tidak valid.',
            'abk_lainnya.required_if'      => 'Kebutuhan khusus lainnya wajib diisi jika memilih Lainnya.',
            'abk_lainnya.max'              => 'Kebutuhan khusus lainnya maksimal 255 karakter.',
            'pekerjaan.required'           => 'Pekerjaan wajib dipilih.',
            'pekerjaan.in'                 => 'Pekerjaan tidak valid.',
            'pekerjaan_lainnya.required_if' => 'Pekerjaan lainnya wajib diisi jika memilih Lainnya.',
            'pekerjaan_lainnya.max'        => 'Pekerjaan lainnya maksimal 255 karakter.',
            'foto.image'                   => 'File foto harus berupa gambar.',
            'foto.mimes'                   => 'Format foto harus: jpg, jpeg, png, atau webp.',
            'foto.max'                     => 'Ukuran foto maksimal 2MB.',
            'status_siswa.required'        => 'Status siswa wajib dipilih.',
            'status_siswa.in'              => 'Status siswa tidak valid.',
            'upload_ktp.file'              => 'File KTP harus berupa file.',
            'upload_ktp.mimes'             => 'Format KTP harus: jpg, jpeg, png, atau pdf.',
            'upload_ktp.max'               => 'Ukuran file KTP maksimal 2MB.',
            'upload_kk.file'               => 'File KK harus berupa file.',
            'upload_kk.mimes'              => 'Format KK harus: jpg, jpeg, png, atau pdf.',
            'upload_kk.max'                => 'Ukuran file KK maksimal 2MB.',
        ];
    }

    public function export(Request $request)
    {
        // Ambil filter opsional dari query string
        $filters = $request->only([
            'status_siswa',
            'status_pembayaran',
            'bidang_studi_id',
            'tgl_mulai',
            'tgl_akhir'
        ]);

        $fileName = 'Data_Lengkap_Siswa_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new SiswaMultiSheetExport($filters), $fileName);
    }
}
