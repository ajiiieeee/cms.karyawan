<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\BidangStudi;
use App\Models\DetailPendaftaran;
use App\Models\KategoriKelas;
use App\Models\LevelKelas;
use App\Models\Pendaftaran;
use App\Models\Siswa;
use Carbon\Carbon;
use App\Jobs\SendPendaftaranEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Exports\PendaftaranMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;

class PendaftaranController extends Controller
{
    public function index(): View
    {
        $bidangStudiList = Cache::remember(
            'master:bidang_studi:by_name',
            86400,
            fn() =>
            BidangStudi::orderBy('nama_bidang_studi')->get()
        );

        return view('pendaftaran.index', compact('bidangStudiList'));
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 2);
        $orderDir      = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortableColumns = [
            0 => 'id',                  // Column 0: No
            1 => 'created_at',          // Column 1: Tgl Pendaftaran
            2 => 'nama_siswa',          // Column 2: Nama Siswa
            3 => 'no_telepon',          // Column 3: No Telepon
            4 => 'id',                  // Column 4: Tempat Daftar
            5 => 'id',                  // Column 5: Aksi
        ];

        $orderColumn = $sortableColumns[$orderColIndex] ?? 'nama_siswa';

        // Ambil siswa yang memiliki riwayat pendaftaran
        $totalRecords = Siswa::has('pendaftaran')->count();

        $query = Siswa::whereHas('pendaftaran')
            ->with(['pendaftaran' => function ($q) {
                $q->latest()->with('detailPendaftaran');
            }]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
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

            $latestPendaftaran = $siswa->pendaftaran->first();
            $encPendaftaranId  = $latestPendaftaran ? IdEncryptor::encrypt($latestPendaftaran->id) : null;

            $tempatDaftar = $latestPendaftaran?->detailPendaftaran->first()->tempat_daftar ?? '-';

            $tanggalPendaftaran = '-';
            if ($latestPendaftaran && $latestPendaftaran->tanggal_pendaftaran) {
                $tanggalPendaftaran = \Carbon\Carbon::parse($latestPendaftaran->tanggal_pendaftaran)->format('d/m/Y');
            }

            $editBtn   = '';
            $deleteBtn = '';
            $detailBtn = $encPendaftaranId
                ? '<a href="' . route('pendaftaran.show', $encPendaftaranId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Detail</a>'
                : '';

            if ($currentUser->hasMenuAccess('pendaftaran', 'edit') && $encPendaftaranId) {
                $editBtn = '<a href="' . route('pendaftaran.edit', $encPendaftaranId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('pendaftaran', 'delete') && $encPendaftaranId) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encPendaftaranId . '" data-name="' . e($siswa->nama_siswa) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'                  => $no,
                'tanggal_pendaftaran' => e($tanggalPendaftaran),
                'nama_siswa'          => e($siswa->nama_siswa ?? '-'),
                'no_telepon'          => e($siswa->no_telepon ?? '-'),
                'tempat_daftar'       => e($tempatDaftar),
                'aksi'                => '<div class="d-inline-flex align-items-center gap-8">' . $detailBtn . $editBtn . $deleteBtn . '</div>',
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
        $bidangStudiList = Cache::remember(
            'master:bidang_studi:by_name',
            86400,
            fn() =>
            BidangStudi::orderBy('nama_bidang_studi')->get()
        );
        $levelKelasList = Cache::remember(
            'master:level_kelas:all',
            86400,
            fn() =>
            LevelKelas::all()
        );
        $kategoriKelasList = Cache::remember(
            'master:kategori_kelas:all',
            86400,
            fn() =>
            KategoriKelas::all()
        );
        $formOptions = $this->getSiswaFormOptions();
        $tempatDaftarOptions = [
            'Nginden' => 'Nginden',
            'Tubanan' => 'Tubanan',
        ];
        return view('pendaftaran.create', compact('bidangStudiList', 'levelKelasList', 'kategoriKelasList') + $formOptions + ['tempatDaftarOptions' => $tempatDaftarOptions]);
    }

    public function store(Request $request): RedirectResponse
    {
        $options = $this->getSiswaFormOptions();

        $validated = $request->validate([
            'tanggal_pendaftaran'              => ['required', 'date_format:d/m/Y'],
            'tempat_daftar'                    => ['nullable', Rule::in(['Nginden', 'Tubanan'])],
            'upload_ktp'                       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'upload_kk'                        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'bukti_pembayaran'                 => ['nullable', 'file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'siswa.nama_siswa'                 => ['required', 'string', 'max:100'],
            // 'siswa.nis'                        => ['required', 'string', 'max:18'],
            'siswa.nik'                        => ['required', 'digits:16', 'unique:siswa,nik'],
            'siswa.tempat_lahir'               => ['required', 'string', 'max:255'],
            'siswa.tanggal_lahir'              => ['required', 'date_format:d/m/Y'],
            'siswa.agama'                      => ['required', Rule::in([...array_keys($options['agamaOptions']), 'lainnya']),],
            'siswa.agama_lainnya' => ['nullable', 'required_if:siswa.agama,lainnya', 'string', 'max:255',],
            'siswa.jenis_kelamin'              => ['required', Rule::in(['laki_laki', 'perempuan'])],
            'siswa.alamat'                     => ['required', 'string', 'max:255'],
            'siswa.no_telepon'                 => ['required', 'digits_between:5,15',],
            'siswa.email'                      => ['required', 'email', 'max:255'],
            'siswa.jenis_tinggal'              => ['required', Rule::in([...array_keys($options['jenisTinggalOptions']), 'lainnya']),],
            'siswa.jenis_tinggal_lainnya'      => ['nullable', 'required_if:siswa.jenis_tinggal,lainnya', 'string', 'max:255'],
            'siswa.abk'                        => ['required', Rule::in([...array_keys($options['abkOptions']), 'lainnya']),],
            'siswa.abk_lainnya'                => ['nullable', 'required_if:siswa.abk,lainnya', 'string', 'max:255'],
            'siswa.pendidikan_terakhir'        => ['required', Rule::in([...array_keys($options['pendidikanOptions']), 'lainnya']),],

            'siswa.pendidikan_terakhir_lainnya' => ['nullable', 'required_if:siswa.pendidikan_terakhir,lainnya', 'string', 'max:255'],
            'siswa.pekerjaan' => ['required', Rule::in([...array_keys($options['pekerjaanOptions']), 'lainnya']),],
            'siswa.pekerjaan_lainnya'          => ['nullable', 'required_if:siswa.pekerjaan,lainnya', 'string', 'max:255'],
            'details'                          => ['required', 'array', 'min:1'],
            'details.*.bidang_studi_id'        => ['required', 'integer', 'exists:bidang_studis,id'],
            'details.*.bidang_studi_custom'    => ['nullable', 'string', 'max:255'],
            'details.*.level_kelas_id'         => ['required', 'integer', 'exists:level_kelas,id'],
            'details.*.kategori_kelas_id'      => ['required', 'integer', 'exists:kategori_kelas,id'],
            'details.*.harga_kursus'           => ['required', 'integer', 'min:0'],
            'details.*.diskon1'                => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.diskon2'                => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.total_harga'            => ['required', 'integer', 'min:0'],
        ], $this->validationMessages());

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;
            $now = Carbon::now();
            $tahun = $now->format('Y');
            $bulan = $now->format('m');
            $prefixTahun = $tahun . $bulan;

            $siswaTerakhir = Siswa::where('nis', 'like', $prefixTahun . '%')
                ->orderByRaw('CAST(RIGHT(nis, 3) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            if ($siswaTerakhir && preg_match('/^\d{6}(\d{3})$/', $siswaTerakhir->nis, $matches)) {
                $nomorUrut = (int) $matches[1] + 1;
            } else {
                $nomorUrut = 1;
            }

            $nis_baru = $tahun
                . $bulan
                . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);

            // Create siswa data
            $siswaData = $validated['siswa'];
            $siswaData['nis'] = $nis_baru;
            $siswaData['tanggal_lahir'] = Carbon::createFromFormat('d/m/Y', $siswaData['tanggal_lahir'])->toDateString();
            if ($siswaData['agama'] !== 'lainnya') {
                $siswaData['agama_lainnya'] = null;
            }
            if ($siswaData['jenis_tinggal'] !== 'lainnya') {
                $siswaData['jenis_tinggal_lainnya'] = null;
            }
            if ($siswaData['abk'] !== 'lainnya') {
                $siswaData['abk_lainnya'] = null;
            }
            if ($siswaData['pendidikan_terakhir'] !== 'lainnya') {
                $siswaData['pendidikan_terakhir_lainnya'] = null;
            }
            if ($siswaData['pekerjaan'] !== 'lainnya') {
                $siswaData['pekerjaan_lainnya'] = null;
            }


            // Handle file uploads for siswa
            if ($request->hasFile('upload_ktp')) {
                $siswaData['upload_ktp'] = $request->file('upload_ktp')->store('siswa/ktp', 'public');
            }
            if ($request->hasFile('upload_kk')) {
                $siswaData['upload_kk'] = $request->file('upload_kk')->store('siswa/kk', 'public');
            }

            $siswa = Siswa::create($siswaData);

            $pendaftaranData = [
                'tanggal_pendaftaran' => Carbon::createFromFormat('d/m/Y', $validated['tanggal_pendaftaran'])->toDateString(),
                'siswa_id'            => $siswa->id,
                'created_by'          => $userName,
                'updated_by'          => $userName,
            ];
            if ($request->hasFile('bukti_pembayaran')) {
                $pendaftaranData['bukti_pembayaran'] = $request->file('bukti_pembayaran')->store('pendaftaran/bukti_pembayaran', 'public');
            }
            $pendaftaran = Pendaftaran::create($pendaftaranData);

            $baseNumber = $this->getNextNoPendaftaranNumber();
            $year = date('Y');
            $prefix = 'CM-' . $year . '-';

            foreach ($validated['details'] as $index => $detail) {
                $pendaftaran->detailPendaftaran()->create([
                    'no_pendaftaran'      => $prefix . str_pad($baseNumber + $index, 3, '0', STR_PAD_LEFT),
                    'siswa_id'            => $siswa->id,
                    'tempat_daftar'       => $validated['tempat_daftar'] ?? null,
                    'bidang_studi_id'     => $detail['bidang_studi_id'],
                    'bidang_studi_custom' => $detail['bidang_studi_custom'] ?? null,
                    'level_kelas_id'      => $detail['level_kelas_id'],
                    'kategori_kelas_id'   => $detail['kategori_kelas_id'],
                    'harga_kursus'        => $detail['harga_kursus'],
                    'diskon1'             => $detail['diskon1'] ?? null,
                    'diskon2'             => $detail['diskon2'] ?? null,
                    'total_harga'         => $detail['total_harga'],
                    'created_by'          => $userName,
                    'updated_by'          => $userName,
                ]);
            }

            DB::commit();
            return redirect()->route('pendaftaran.index')
                ->with('success', 'Data pendaftaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pendaftaran store error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran.');
        }
    }

    public function publicIndex()
    {
        $bidangStudis = Cache::remember(
            'master:bidang_studi:by_id',
            86400,
            fn() =>
            BidangStudi::orderBy('id', 'asc')->get()
        );
        $levels = Cache::remember(
            'master:level_kelas:all',
            86400,
            fn() =>
            LevelKelas::all()
        );
        $kategoriKelas = Cache::remember(
            'master:kategori_kelas:all',
            86400,
            fn() =>
            KategoriKelas::all()
        );

        return view('pendaftaran.nginden.pendaftaran-nginden', compact('bidangStudis', 'levels', 'kategoriKelas'));
    }

    public function publicStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Siswa
            'nama_siswa'                 => ['required', 'string', 'max:100'],
            'nik'                        => ['required', 'digits:16', 'unique:siswa,nik',],
            'tempat_lahir'               => ['required', 'string', 'max:255'],
            'tanggal_lahir'              => ['required', 'date_format:d/m/Y'],
            'agama'                      => ['required', 'string', 'max:50'],
            'agama_lainnya'              => ['nullable', 'required_if:agama,lainnya', 'string', 'max:255'],
            'jenis_kelamin'              => ['required', Rule::in(['laki_laki', 'perempuan'])],
            'alamat'                     => ['required', 'string', 'max:255'],
            'kota'                       => ['required', 'string', 'max:100'],
            'provinsi'                   => ['required', 'string', 'max:100'],
            'no_telepon'                 => ['required', 'string', 'max:20'],
            'email'                      => ['nullable', 'email', 'max:255'],
            'jenis_tinggal'              => ['required', 'string', 'max:100'],
            'jenis_tinggal_lainnya'      => ['nullable', 'string', 'max:255'],
            'abk'                        => ['required', 'string', 'max:100'],
            'abk_lainnya'                => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir'        => ['required', 'string', 'max:100'],
            'pendidikan_terakhir_lainnya' => ['nullable', 'string', 'max:255'],
            'pekerjaan'                  => ['required', 'string', 'max:100'],
            'pekerjaan_lainnya'          => ['nullable', 'string', 'max:255'],
            'upload_ktp'                 => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'upload_kk'                  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            // Course detail
            'id_bidang_studi'            => ['required', 'integer', 'exists:bidang_studis,id'],
            'nama_bidang_studi'          => ['nullable', 'string', 'max:255'],
            'keterangan_bidang_studi'    => ['nullable', 'string', 'max:255'],
            'id_level_kelas'             => ['required', 'integer', 'exists:level_kelas,id'],
            'id_kategori_kelas'          => ['required', 'integer', 'exists:kategori_kelas,id'],
            // Payment & other
            'upload_pembayaran'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'tempat_daftar'              => ['required', Rule::in(['Nginden', 'Tubanan'])],
        ], [
            'nik.unique'   => 'NIK/No. Identitas ini sudah terdaftar. Silakan gunakan NIK lain.',
            'nik.digits'   => 'NIK/No. Identitas harus tepat 16 digit.',
            'nik.required' => 'NIK/No. Identitas wajib diisi.',
        ]);

        DB::beginTransaction();
try {

    $now = Carbon::now();

    $tahun = $now->format('Y');
    $bulan = $now->format('m');

    $prefixTahun = $tahun . $bulan;

    // Ambil NIS terakhir pada tahun dan bulan yang sama
    $siswaTerakhir = Siswa::where('nis', 'like', $prefixTahun . '%')
        ->orderByRaw('CAST(RIGHT(nis, 3) AS UNSIGNED) DESC')
        ->lockForUpdate()
        ->first();

    if ($siswaTerakhir && preg_match('/^\d{6}(\d{3})$/', $siswaTerakhir->nis, $matches)) {
        $nomorUrut = (int) $matches[1] + 1;
    } else {
        $nomorUrut = 1;
    }

    $nis_baru = $tahun
        . $bulan
        . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);


            // ==========================================
            // PARSE TANGGAL LAHIR
            // ==========================================

            $tanggalLahir = $this->parseTanggalLahir($request->tanggal_lahir);

            if (!$tanggalLahir) {
                $this->logPendaftaran('ERROR', 'Format tanggal lahir tidak valid', [
                    'tanggal_lahir_raw' => $request->tanggal_lahir,
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->back()
                    ->withErrors([
                        'tanggal_lahir' => 'Format tanggal lahir tidak valid. Gunakan format dd/mm/yyyy.'
                    ])
                    ->withInput();
            }

            $telepon = $validated['no_telepon'] ?? '';

            $siswaData = [
                'nama_siswa'                  => $validated['nama_siswa'],
                'nis'                         => $nis_baru,
                'nik'                         => $validated['nik'],
                'jenis_kelamin'               => $validated['jenis_kelamin'],
                'tempat_lahir'                => $validated['tempat_lahir'],
                'tanggal_lahir'               => $tanggalLahir,
                'agama'                       => $validated['agama'],
                'agama_lainnya'               => strtolower($validated['agama']) === 'lainnya' ? ($validated['agama_lainnya'] ?? null) : null,
                'email'                       => $validated['email'] ?? null,
                'no_telepon'                  => $telepon,
                'alamat'                      => $validated['alamat'],
                'kota'                        => $validated['kota'] ?? null,
                'provinsi'                    => $validated['provinsi'] ?? null,
                'jenis_tinggal'               => $validated['jenis_tinggal'],
                'jenis_tinggal_lainnya'       => strtolower($validated['jenis_tinggal']) === 'lainnya'
                    ? ($validated['jenis_tinggal_lainnya'] ?? null)
                    : null,
                'pendidikan_terakhir'         => $validated['pendidikan_terakhir'],
                'pendidikan_terakhir_lainnya' => strtolower($validated['pendidikan_terakhir']) === 'lainnya'
                    ? ($validated['pendidikan_terakhir_lainnya'] ?? null)
                    : null,
                'abk'                         => $validated['abk'],
                'abk_lainnya'                 => strtolower($validated['abk']) === 'lainnya'
                    ? ($validated['abk_lainnya'] ?? null)
                    : null,
                'pekerjaan'                   => $validated['pekerjaan'],
                'pekerjaan_lainnya'           => strtolower($validated['pekerjaan']) === 'lainnya'
                    ? ($validated['pekerjaan_lainnya'] ?? null)
                    : null,
                'status_siswa'                => false,
            ];

            if ($request->hasFile('upload_ktp')) {
                $siswaData['upload_ktp'] = $request->file('upload_ktp')->store('siswa/ktp', 'public');
            }
            if ($request->hasFile('upload_kk')) {
                $siswaData['upload_kk'] = $request->file('upload_kk')->store('siswa/kk', 'public');
            }

            $siswa = Siswa::create($siswaData);

            $today = Carbon::today();

            $pendaftaranData = [
                'tanggal_pendaftaran' => $today->format('Y-m-d'),
                'siswa_id'            => $siswa->id,
                'created_by'          => 'public',
                'updated_by'          => 'public',
            ];
            if ($request->hasFile('bukti_pembayaran')) {
                $pembayaranFile = $request->file('bukti_pembayaran');
                $ext = $pembayaranFile->getClientOriginalExtension() ?: 'jpg';
                $pembayaranFileName = 'pembayaran_' . $siswa->id . '_' . time() . '.' . $ext;
                $uploadPembayaranPath = $pembayaranFile->storeAs('pendaftaran/bukti_pembayaran', $pembayaranFileName, 'public');

                if ($uploadPembayaranPath) {
                    $pendaftaranData['bukti_pembayaran'] = $uploadPembayaranPath;
                }
            }

            $pendaftaran = Pendaftaran::create($pendaftaranData);

            $baseNumber = $this->getNextNoPendaftaranNumber();
            $noPendaftaran = 'CM-' . date('Y') . '-' . str_pad($baseNumber, 3, '0', STR_PAD_LEFT);

            // bidang_studi_custom is set only when user typed a custom name (nama_bidang_studi === 'Lainnya')
            $namaBidangStudi  = $validated['nama_bidang_studi'] ?? null;
            $bidangStudiCustom = ($namaBidangStudi === 'Lainnya')
                ? ($validated['keterangan_bidang_studi'] ?? null)
                : null;

            $pendaftaran->detailPendaftaran()->create([
                'no_pendaftaran'      => $noPendaftaran,
                'siswa_id'            => $siswa->id,
                'tempat_daftar'       => $validated['tempat_daftar'],
                'bidang_studi_id'     => $validated['id_bidang_studi'],
                'bidang_studi_custom' => $bidangStudiCustom,
                'level_kelas_id'      => $validated['id_level_kelas'],
                'kategori_kelas_id'   => $validated['id_kategori_kelas'],
                'harga_kursus'        => 0,
                'diskon1'             => null,
                'diskon2'             => null,
                'total_harga'         => 0,
                'created_by'          => 'publik',
                'updated_by'          => 'publik',
            ]);

            DB::commit();

            // Dispatch email job to queue (runs in background)
            try {
                SendPendaftaranEmail::dispatch(
                    $siswa->id,
                    $pendaftaran->id,
                    $uploadPembayaranPath
                );
            } catch (\Throwable $emailEx) {
                // Email dispatch gagal tidak boleh menggagalkan pendaftaran
                Log::error('[Pendaftaran] Gagal dispatch email job', [
                    'siswa_id'       => $siswa->id,
                    'pendaftaran_id' => $pendaftaran->id,
                    'error'          => $emailEx->getMessage(),
                ]);
            }
            return redirect()->route('pendaftaran.success')
                ->with('success', 'Pendaftaran berhasil dikirim! Tim kami akan segera menghubungi Anda.')
                ->with('form_source', 'nginden');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Public pendaftaran store error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran. Silahkan coba lagi.');
        }
    }

    public function sukses()
    {
        return view('pendaftaran.nginden.pendaftaran-sukses');
    }

    public function publicMemberCreate()
    {
        $bidangStudis   = Cache::remember(
            'master:bidang_studi:by_id',
            86400,
            fn() =>
            BidangStudi::orderBy('id', 'asc')->get()
        );
        $levels         = Cache::remember(
            'master:level_kelas:all',
            86400,
            fn() =>
            LevelKelas::all()
        );
        $kategoriKelas  = Cache::remember(
            'master:kategori_kelas:all',
            86400,
            fn() =>
            KategoriKelas::all()
        );

        return view('pendaftaran.nginden.pendaftaran-nginden-member', compact('bidangStudis', 'levels', 'kategoriKelas'));
    }

    public function publicCekNik(Request $request): JsonResponse
    {
        $nik = trim((string) $request->input('nik', ''));
        if (!$nik) {
            return response()->json(['found' => false]);
        }

        $siswa = Siswa::where('nik', $nik)->first();
        if (!$siswa) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'siswa' => [
                'id'           => $siswa->id,
                'nama_siswa'   => $siswa->nama_siswa,
                'nik'          => $siswa->nik,
                'jenis_kelamin' => $siswa->jenis_kelamin === 'laki_laki' ? 'Laki-laki' : 'Perempuan',
                'tempat_lahir' => $siswa->tempat_lahir,
                'tanggal_lahir' => $siswa->tanggal_lahir
                    ? Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y')
                    : '-',
                'email'        => $siswa->email ?? '-',
                'no_telepon'   => $siswa->no_telepon,
                'alamat'       => $siswa->alamat,
            ],
        ]);
    }

    public function publicMemberStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id'                => ['required', 'integer', 'exists:siswa,id'],
            'id_bidang_studi'         => ['required', 'integer', 'exists:bidang_studis,id'],
            'nama_bidang_studi'       => ['nullable', 'string', 'max:255'],
            'keterangan_bidang_studi' => ['nullable', 'string', 'max:255'],
            'id_level_kelas'          => ['required', 'integer', 'exists:level_kelas,id'],
            'id_kategori_kelas'       => ['required', 'integer', 'exists:kategori_kelas,id'],
            'upload_ktp'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'upload_kk'               => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'upload_pembayaran'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'tempat_daftar'           => ['required', Rule::in(['Nginden', 'Tubanan'])],
        ], [
            'siswa_id.required'           => 'Data siswa tidak ditemukan. Silakan masukkan No. Identitas yang valid.',
            'siswa_id.exists'             => 'Data siswa tidak ditemukan di sistem.',
            'id_bidang_studi.required'    => 'Bidang studi kursus wajib dipilih.',
            'id_bidang_studi.exists'      => 'Bidang studi yang dipilih tidak valid.',
            'id_level_kelas.required'     => 'Level kursus wajib dipilih.',
            'id_level_kelas.exists'       => 'Level kursus yang dipilih tidak valid.',
            'id_kategori_kelas.required'  => 'Pilihan kelas wajib dipilih.',
            'id_kategori_kelas.exists'    => 'Pilihan kelas tidak valid.',
            'upload_ktp.mimes'            => 'Format file identitas harus JPG, JPEG, PNG, atau PDF.',
            'upload_ktp.max'              => 'Ukuran file identitas maksimal 2MB.',
            'upload_kk.required'          => 'File Kartu Keluarga wajib diupload.',
            'upload_kk.mimes'             => 'Format file Kartu Keluarga harus JPG, JPEG, PNG, atau PDF.',
            'upload_kk.max'               => 'Ukuran file Kartu Keluarga maksimal 2MB.',
            'upload_pembayaran.mimes'     => 'Format bukti pembayaran harus JPG, JPEG, PNG, atau PDF.',
            'upload_pembayaran.max'       => 'Ukuran file bukti pembayaran maksimal 2MB.',
            'tempat_daftar.required'      => 'Tempat daftar wajib dipilih.',
            'tempat_daftar.in'            => 'Tempat daftar tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $siswa = Siswa::findOrFail($validated['siswa_id']);

            // Update dokumen siswa jika diupload ulang
            if ($request->hasFile('upload_ktp')) {
                if ($siswa->upload_ktp) {
                    Storage::disk('public')->delete($siswa->upload_ktp);
                }
                $siswa->upload_ktp = $request->file('upload_ktp')->store('siswa/ktp', 'public');
                $siswa->save();
            }
            if ($request->hasFile('upload_kk')) {
                if ($siswa->upload_kk) {
                    Storage::disk('public')->delete($siswa->upload_kk);
                }
                $siswa->upload_kk = $request->file('upload_kk')->store('siswa/kk', 'public');
                $siswa->save();
            }

            $today = Carbon::today();
            $pendaftaranData = [
                'tanggal_pendaftaran' => $today->format('Y-m-d'),
                'siswa_id'            => $siswa->id,
                'created_by'          => 'public',
                'updated_by'          => 'public',
            ];
            if ($request->hasFile('upload_pembayaran')) {
                $pendaftaranData['bukti_pembayaran'] = $request->file('upload_pembayaran')
                    ->store('pendaftaran/bukti_pembayaran', 'public');
            }
            $pendaftaran = Pendaftaran::create($pendaftaranData);

            $baseNumber    = $this->getNextNoPendaftaranNumber();
            $noPendaftaran = 'CM-' . date('Y') . '-' . str_pad($baseNumber, 3, '0', STR_PAD_LEFT);

            $namaBidangStudi  = $validated['nama_bidang_studi'] ?? null;
            $bidangStudiCustom = ($namaBidangStudi === 'Lainnya')
                ? ($validated['keterangan_bidang_studi'] ?? null)
                : null;

            $pendaftaran->detailPendaftaran()->create([
                'no_pendaftaran'      => $noPendaftaran,
                'siswa_id'            => $siswa->id,
                'tempat_daftar'       => $validated['tempat_daftar'],
                'bidang_studi_id'     => $validated['id_bidang_studi'],
                'bidang_studi_custom' => $bidangStudiCustom,
                'level_kelas_id'      => $validated['id_level_kelas'],
                'kategori_kelas_id'   => $validated['id_kategori_kelas'],
                'harga_kursus'        => 0,
                'diskon1'             => null,
                'diskon2'             => null,
                'total_harga'         => 0,
                'created_by'          => 'publik',
                'updated_by'          => 'publik',
            ]);

            DB::commit();
            return redirect()->route('pendaftaran.success')
                ->with('success', 'Pendaftaran kursus berhasil dikirim! Tim kami akan segera menghubungi Anda.')
                ->with('form_source', 'nginden.member');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Public member store error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silahkan coba lagi.');
        }
    }


    public function show(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        // Mendeteksi apakah $id berasal dari Pendaftaran atau langsung dari Siswa
        $pendaftaran = Pendaftaran::find($id);
        $siswaId = $pendaftaran ? $pendaftaran->siswa_id : $id;

        // Ambil data Siswa beserta SELURUH riwayat pendaftaran & detail kursusnya
        $siswa = Siswa::with([
            'pendaftaran' => function ($q) {
                $q->orderBy('tanggal_pendaftaran', 'desc');
            },
            'pendaftaran.detailPendaftaran.bidangStudi',
            'pendaftaran.detailPendaftaran.levelKelas',
            'pendaftaran.detailPendaftaran.kategoriKelas'
        ])->findOrFail($siswaId);

        // Menyajikan pendaftaran terbaru untuk data pendukung
        $latestPendaftaran = $siswa->pendaftaran->first();

        return view('pendaftaran.show', compact('siswa', 'latestPendaftaran'));
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pendaftaran = Pendaftaran::findOrFail($id);

        // Ambil semua detail kursus milik siswa ini dari seluruh pendaftarannya
        $allDetails = DetailPendaftaran::where('siswa_id', $pendaftaran->siswa_id)->get();

        // Set relasi detailPendaftaran secara manual agar menyertakan seluruh kursus siswa
        $pendaftaran->setRelation('detailPendaftaran', $allDetails);

        $bidangStudiList = Cache::remember(
            'master:bidang_studi:by_name',
            86400,
            fn() =>
            BidangStudi::orderBy('nama_bidang_studi')->get()
        );
        $levelKelasList = Cache::remember(
            'master:level_kelas:all',
            86400,
            fn() =>
            LevelKelas::all()
        );
        $kategoriKelasList = Cache::remember(
            'master:kategori_kelas:all',
            86400,
            fn() =>
            KategoriKelas::all()
        );

        $formOptions = $this->getSiswaFormOptions();
        $tempatDaftarOptions = [
            'Nginden' => 'Nginden',
            'Tubanan' => 'Tubanan',
        ];

        return view('pendaftaran.edit', compact(
            'pendaftaran',
            'bidangStudiList',
            'levelKelasList',
            'kategoriKelasList',
            'tempatDaftarOptions'
        ) + $formOptions);
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pendaftaran = Pendaftaran::findOrFail($id);
        $siswa = Siswa::findOrFail($pendaftaran->siswa_id);

        $options = $this->getSiswaFormOptions();

        $validated = $request->validate([
            'tanggal_pendaftaran'               => ['required', 'date_format:d/m/Y'],
            'tempat_daftar'                     => ['nullable', Rule::in(['Nginden', 'Tubanan'])],
            'upload_ktp'                        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'upload_kk'                         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'siswa.nama_siswa'                 => ['required', 'string', 'max:100'],
            'siswa.nik'                         => ['required', 'digits:16', Rule::unique('siswa', 'nik')->ignore($siswa->id)],
            'siswa.tempat_lahir'                => ['required', 'string', 'max:255'],
            'siswa.tanggal_lahir'               => ['required', 'date_format:d/m/Y'],
            'siswa.agama'                       => ['required', Rule::in([...array_keys($options['agamaOptions']), 'lainnya'])],
            'siswa.agama_lainnya'               => ['nullable', 'required_if:siswa.agama,lainnya', 'string', 'max:255'],
            'siswa.jenis_kelamin'               => ['required', Rule::in(['laki_laki', 'perempuan'])],
            'siswa.alamat'                      => ['required', 'string', 'max:255'],
            'siswa.no_telepon'                  => ['required', 'digits_between:5,15'],
            'siswa.email'                       => ['required', 'email', 'max:255'],
            'siswa.jenis_tinggal'               => ['required', Rule::in([...array_keys($options['jenisTinggalOptions']), 'lainnya'])],
            'siswa.jenis_tinggal_lainnya'       => ['nullable', 'required_if:siswa.jenis_tinggal,lainnya', 'string', 'max:255'],
            'siswa.abk'                         => ['required', Rule::in([...array_keys($options['abkOptions']), 'lainnya'])],
            'siswa.abk_lainnya'                 => ['nullable', 'required_if:siswa.abk,lainnya', 'string', 'max:255'],
            'siswa.pendidikan_terakhir'         => ['required', Rule::in([...array_keys($options['pendidikanOptions']), 'lainnya'])],
            'siswa.pendidikan_terakhir_lainnya'  => ['nullable', 'required_if:siswa.pendidikan_terakhir,lainnya', 'string', 'max:255'],
            'siswa.pekerjaan'                   => ['required', Rule::in([...array_keys($options['pekerjaanOptions']), 'lainnya'])],
            'siswa.pekerjaan_lainnya'           => ['nullable', 'required_if:siswa.pekerjaan,lainnya', 'string', 'max:255'],
            'details'                           => ['required', 'array', 'min:1'],
            'details.*.bidang_studi_id'         => ['required', 'integer', 'exists:bidang_studis,id'],
            'details.*.bidang_studi_custom'     => ['nullable', 'string', 'max:255'],
            'details.*.level_kelas_id'          => ['required', 'integer', 'exists:level_kelas,id'],
            'details.*.kategori_kelas_id'       => ['required', 'integer', 'exists:kategori_kelas,id'],
            'details.*.harga_kursus'            => ['required', 'integer', 'min:0'],
            'details.*.diskon1'                 => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.diskon2'                 => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.total_harga'             => ['required', 'integer', 'min:0'],
        ], $this->validationMessages());

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;

            // 1. Update Data Siswa
            $siswaData = $validated['siswa'];
            $siswaData['tanggal_lahir'] = Carbon::createFromFormat('d/m/Y', $siswaData['tanggal_lahir'])->toDateString();

            if ($siswaData['agama'] !== 'lainnya') {
                $siswaData['agama_lainnya'] = null;
            }
            if ($siswaData['jenis_tinggal'] !== 'lainnya') {
                $siswaData['jenis_tinggal_lainnya'] = null;
            }
            if ($siswaData['abk'] !== 'lainnya') {
                $siswaData['abk_lainnya'] = null;
            }
            if ($siswaData['pendidikan_terakhir'] !== 'lainnya') {
                $siswaData['pendidikan_terakhir_lainnya'] = null;
            }
            if ($siswaData['pekerjaan'] !== 'lainnya') {
                $siswaData['pekerjaan_lainnya'] = null;
            }

            if ($request->hasFile('upload_ktp')) {
                if ($siswa->upload_ktp) {
                    Storage::disk('public')->delete($siswa->upload_ktp);
                }
                $siswaData['upload_ktp'] = $request->file('upload_ktp')->store('siswa/ktp', 'public');
            }
            if ($request->hasFile('upload_kk')) {
                if ($siswa->upload_kk) {
                    Storage::disk('public')->delete($siswa->upload_kk);
                }
                $siswaData['upload_kk'] = $request->file('upload_kk')->store('siswa/kk', 'public');
            }

            $siswa->update($siswaData);

            // 2. Update Header Pendaftaran Utama
            $pendaftaran->update([
                'tanggal_pendaftaran' => Carbon::createFromFormat('d/m/Y', $validated['tanggal_pendaftaran'])->toDateString(),
                'updated_by'          => $userName,
            ]);

            // 3. Ambil SELURUH Detail Pendaftaran milik Siswa ini
            $existingDetails = DetailPendaftaran::where('siswa_id', $siswa->id)
                ->with(['pembayaran', 'penjadwalan'])
                ->get()
                ->keyBy('id');

            $detailsWithRelations = [];
            $detailsWithoutRelations = [];

            foreach ($existingDetails as $existing) {
                $hasPembayaran  = $existing->pembayaran !== null && $existing->pembayaran->exists;
                $hasPenjadwalan = $existing->penjadwalan !== null && $existing->penjadwalan->exists;

                if ($hasPembayaran || $hasPenjadwalan) {
                    $detailsWithRelations[$existing->id] = $existing;
                } else {
                    $detailsWithoutRelations[] = $existing->id;
                }
            }

            // Hapus detail yang belum berkategori transaksi (pembayaran/jadwal) jika tidak digunakan
            if (!empty($detailsWithoutRelations)) {
                DetailPendaftaran::whereIn('id', $detailsWithoutRelations)->delete();
            }

            $year       = date('Y');
            $prefix     = 'CM-' . $year . '-';
            $baseNumber = $this->getNextNoPendaftaranNumber();
            $counter    = $baseNumber;

            $existingDetailIds   = array_keys($detailsWithRelations);
            $existingDetailIndex = 0;

            // 4. Update atau Tambah Kursus-kursus Baru
            foreach ($validated['details'] as $index => $detail) {
                if ($index < count($existingDetailIds) && $existingDetailIndex < count($existingDetailIds)) {
                    $existingDetailId = $existingDetailIds[$existingDetailIndex];
                    $existingDetail   = $detailsWithRelations[$existingDetailId];
                    $existingDetailIndex++;

                    $existingDetail->update([
                        'bidang_studi_id'     => $detail['bidang_studi_id'],
                        'bidang_studi_custom' => $detail['bidang_studi_custom'] ?? null,
                        'level_kelas_id'      => $detail['level_kelas_id'],
                        'kategori_kelas_id'   => $detail['kategori_kelas_id'],
                        'harga_kursus'        => $detail['harga_kursus'],
                        'diskon1'             => $detail['diskon1'] ?? null,
                        'diskon2'             => $detail['diskon2'] ?? null,
                        'total_harga'         => $detail['total_harga'],
                        'updated_by'          => $userName,
                    ]);
                } else {
                    $noPendaftaran = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
                    $counter++;

                    DetailPendaftaran::create([
                        'no_pendaftaran'      => $noPendaftaran,
                        'pendaftaran_id'      => $pendaftaran->id,
                        'siswa_id'            => $siswa->id,
                        'tempat_daftar'       => $validated['tempat_daftar'] ?? null,
                        'bidang_studi_id'     => $detail['bidang_studi_id'],
                        'bidang_studi_custom' => $detail['bidang_studi_custom'] ?? null,
                        'level_kelas_id'      => $detail['level_kelas_id'],
                        'kategori_kelas_id'   => $detail['kategori_kelas_id'],
                        'harga_kursus'        => $detail['harga_kursus'],
                        'diskon1'             => $detail['diskon1'] ?? null,
                        'diskon2'             => $detail['diskon2'] ?? null,
                        'total_harga'         => $detail['total_harga'],
                        'created_by'          => $userName,
                        'updated_by'          => $userName,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('pendaftaran.index')
                ->with('success', 'Data pendaftaran siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data pendaftaran: ' . $e->getMessage());
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pendaftaran = Pendaftaran::findOrFail($id);

        DB::beginTransaction();
        try {
            $pendaftaran->delete();
            DB::commit();
            return redirect()->route('pendaftaran.index')
                ->with('success', 'Data pendaftaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data pendaftaran.');
        }
    }

    public function searchSiswa(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));
        if (!$search) {
            return response()->json(['results' => []]);
        }

        // Jika parameter pencarian berupa ID atau dikirim khusus param id
        $siswaList = Siswa::where('id', $search)
            ->orWhere('nama_siswa', 'like', "%{$search}%")
            ->orWhere('nik', 'like', "%{$search}%")
            ->orderBy('nama_siswa')
            ->limit(20)
            ->get(['id', 'nama_siswa', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'agama_lainnya', 'email', 'no_telepon', 'alamat', 'jenis_tinggal', 'jenis_tinggal_lainnya', 'pendidikan_terakhir', 'pekerjaan', 'abk', 'abk_lainnya']);
        $results = $siswaList->map(function ($s) {
            $jk = $s->jenis_kelamin === 'laki_laki' ? 'L' : 'P';
            return [
                'id'   => $s->id,
                'text' => $s->nama_siswa . ' (' . $jk . ') — NIK: ' . $s->nik,
                'nama_siswa'          => $s->nama_siswa,
                'nik'                 => $s->nik,
                'jenis_kelamin'       => $s->jenis_kelamin,
                'tempat_lahir'        => $s->tempat_lahir,
                'tanggal_lahir'       => $s->tanggal_lahir,
                'agama'               => $s->agama,
                'email'               => $s->email,
                'no_telepon'          => $s->no_telepon,
                'alamat'              => $s->alamat,
                'pendidikan_terakhir' => $s->pendidikan_terakhir,
                'pekerjaan'           => $s->pekerjaan,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function memberCreate(): View
    {
        $bidangStudiList = Cache::remember(
            'master:bidang_studi:by_name',
            86400,
            fn() =>
            BidangStudi::orderBy('nama_bidang_studi')->get()
        );
        $levelKelasList = Cache::remember(
            'master:level_kelas:all',
            86400,
            fn() =>
            LevelKelas::all()
        );
        $kategoriKelasList = Cache::remember(
            'master:kategori_kelas:all',
            86400,
            fn() =>
            KategoriKelas::all()
        );
        $tempatDaftarOptions = [
            'Nginden' => 'Nginden',
            'Tubanan' => 'Tubanan',
        ];

        return view('pendaftaran.create-member', compact('bidangStudiList', 'levelKelasList', 'kategoriKelasList', 'tempatDaftarOptions'));
    }

    public function memberStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pendaftaran'              => ['required', 'date_format:d/m/Y'],
            'siswa_id'                         => ['required', 'integer', 'exists:siswa,id'],
            'tempat_daftar'                    => ['nullable', Rule::in(['Nginden', 'Tubanan'])],
            'bukti_pembayaran'                 => ['nullable', 'file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'details'                          => ['required', 'array', 'min:1'],
            'details.*.bidang_studi_id'        => ['required', 'integer', 'exists:bidang_studis,id'],
            'details.*.bidang_studi_custom'    => ['nullable', 'string', 'max:255'],
            'details.*.level_kelas_id'         => ['required', 'integer', 'exists:level_kelas,id'],
            'details.*.kategori_kelas_id'      => ['required', 'integer', 'exists:kategori_kelas,id'],
            'details.*.harga_kursus'           => ['required', 'integer', 'min:0'],
            'details.*.diskon1'                => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.diskon2'                => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.total_harga'            => ['required', 'integer', 'min:0'],
        ], [
            'tanggal_pendaftaran.required'           => 'Tanggal pendaftaran wajib diisi.',
            'tanggal_pendaftaran.date_format'         => 'Format tanggal pendaftaran harus dd/mm/yyyy.',
            'siswa_id.required'                      => 'Siswa wajib dipilih.',
            'siswa_id.exists'                        => 'Siswa yang dipilih tidak valid.',
            'details.required'                        => 'Minimal harus ada 1 detail kursus.',
            'details.min'                             => 'Minimal harus ada 1 detail kursus.',
            'details.*.bidang_studi_id.required'      => 'Bidang studi wajib dipilih pada setiap detail.',
            'details.*.bidang_studi_id.exists'        => 'Bidang studi yang dipilih tidak valid.',
            'details.*.level_kelas_id.required'       => 'Level kelas wajib dipilih pada setiap detail.',
            'details.*.level_kelas_id.exists'         => 'Level kelas yang dipilih tidak valid.',
            'details.*.kategori_kelas_id.required'    => 'Kategori kelas wajib dipilih pada setiap detail.',
            'details.*.kategori_kelas_id.exists'      => 'Kategori kelas yang dipilih tidak valid.',
            'details.*.harga_kursus.required'         => 'Harga kursus wajib diisi pada setiap detail.',
            'details.*.harga_kursus.integer'          => 'Harga kursus harus berupa angka.',
            'details.*.total_harga.required'          => 'Total harga wajib diisi pada setiap detail.',
            'details.*.total_harga.integer'           => 'Total harga harus berupa angka.',
            'bukti_pembayaran.mimes'                   => 'Format bukti pembayaran harus JPG, JPEG, atau PDF.',
            'bukti_pembayaran.max'                     => 'Ukuran file bukti pembayaran maksimal 2MB.',
        ]);

        // Check duplicate bidang studi + level kelas (within form + existing DB records for this siswa)
        $duplicateErrors = $this->checkDuplicateDetails($validated['details'], $validated['siswa_id']);
        if (!empty($duplicateErrors)) {
            return redirect()->back()->withInput()->withErrors($duplicateErrors);
        }

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;
            $siswa = Siswa::findOrFail($validated['siswa_id']);

            $uploadPembayaranPath = null;
            $pendaftaranData = [
                'tanggal_pendaftaran' => Carbon::createFromFormat('d/m/Y', $validated['tanggal_pendaftaran'])->toDateString(),
                'siswa_id'            => $siswa->id,
                'created_by'          => $userName,
                'updated_by'          => $userName,
            ];
            if ($request->hasFile('bukti_pembayaran')) {
                $pembayaranFile = $request->file('bukti_pembayaran');
                $ext = $pembayaranFile->getClientOriginalExtension() ?: 'jpg';
                $pembayaranFileName = 'pembayaran_' . $siswa->id . '_' . time() . '.' . $ext;
                $uploadPembayaranPath = $pembayaranFile->storeAs('pendaftaran/bukti_pembayaran', $pembayaranFileName, 'public');

                if ($uploadPembayaranPath) {
                    $pendaftaranData['bukti_pembayaran'] = $uploadPembayaranPath;
                }
            }

            $pendaftaran = Pendaftaran::create($pendaftaranData);

            $baseNumber = $this->getNextNoPendaftaranNumber();
            $year = date('Y');
            $prefix = 'CM-' . $year . '-';

            foreach ($validated['details'] as $index => $detail) {
                $pendaftaran->detailPendaftaran()->create([
                    'no_pendaftaran'      => $prefix . str_pad($baseNumber + $index, 3, '0', STR_PAD_LEFT),
                    'siswa_id'            => $siswa->id,
                    'tempat_daftar'       => $validated['tempat_daftar'] ?? null,
                    'bidang_studi_id'     => $detail['bidang_studi_id'],
                    'bidang_studi_custom' => $detail['bidang_studi_custom'] ?? null,
                    'level_kelas_id'      => $detail['level_kelas_id'],
                    'kategori_kelas_id'   => $detail['kategori_kelas_id'],
                    'harga_kursus'        => $detail['harga_kursus'],
                    'diskon1'             => $detail['diskon1'] ?? null,
                    'diskon2'             => $detail['diskon2'] ?? null,
                    'total_harga'         => $detail['total_harga'],
                    'created_by'          => $userName,
                    'updated_by'          => $userName,
                ]);
            }

            DB::commit();

            // Dispatch email job to queue (runs in background)
            try {
                SendPendaftaranEmail::dispatch(
                    $siswa->id,
                    $pendaftaran->id,
                    $uploadPembayaranPath
                );
            } catch (\Throwable $emailEx) {
                // Email dispatch gagal tidak boleh menggagalkan pendaftaran
                Log::error('[Pendaftaran] Gagal dispatch email job', [
                    'siswa_id'       => $siswa->id,
                    'pendaftaran_id' => $pendaftaran->id,
                    'error'          => $emailEx->getMessage(),
                ]);
            }

            return redirect()->route('pendaftaran.index')
                ->with('success', 'Data pendaftaran member berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Member store error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran member.');
        }
    }

    private function logPendaftaran(string $level, string $message, array $context = []): void
    {
        $logMessage = '[Pendaftaran] ' . $message;
        match (strtoupper($level)) {
            'ERROR'            => Log::error($logMessage, $context),
            'WARN', 'WARNING'  => Log::warning($logMessage, $context),
            'DEBUG'            => Log::debug($logMessage, $context),
            default            => Log::info($logMessage, $context),
        };
    }

    private function getNextNoPendaftaranNumber(): int
    {
        $prefix = 'CM-' . date('Y') . '-';

        $last = DetailPendaftaran::where('no_pendaftaran', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(no_pendaftaran, -3) AS UNSIGNED) DESC')
            ->value('no_pendaftaran');

        return $last ? (int) substr($last, -3) + 1 : 1;
    }

    private function getSiswaFormOptions(): array
    {
        return [
            'agamaOptions' => [
                'islam' => 'Islam',
                'kristen' => 'Kristen',
                'katolik' => 'Katolik',
                'hindu' => 'Hindu',
                'buddha' => 'Buddha',
                'konghucu' => 'Konghucu',
            ],
            'jenisTinggalOptions' => [
                'rumah_sendiri' => 'Rumah Sendiri',
                'rumah_orang_tua' => 'Rumah Orang Tua',
                'kos' => 'Kos/Kontrakan',
                'rumah_saudara' => 'Rumah Saudara',
                'asrama' => 'Asrama',
            ],
            'abkOptions' => [
                'tidak_ada' => 'Tidak Ada',
                'tunanetra' => 'Tunanetra',
                'tunarungu' => 'Tunarungu',
                'tunawicara' => 'Tunawicara',
                'tunadaksa' => 'Tunadaksa',
                'tunagrahita' => 'Tunagrahita',
                'tunalaras' => 'Tunalaras',
                'autis' => 'Autis',
                'adhd' => 'ADHD',
            ],
            'pendidikanOptions' => [
                'sd' => 'SD',
                'smp' => 'SMP',
                'sma_smk' => 'SMA/SMK',
                'd1' => 'D1',
                'd2' => 'D2',
                'd3' => 'D3',
                'd4/s1' => 'D4/S1',
                's2' => 'S2',
                's3' => 'S3',

            ],
            'pekerjaanOptions' => [
                'pelajar' => 'Pelajar/Mahasiswa',
                'pns' => 'PNS',
                'karyawan_swasta' => 'Karyawan Swasta',
                'wiraswasta' => 'Wiraswasta',
                'freelancer' => 'Freelancer',
                'tidak_bekerja' => 'Tidak Bekerja',

            ],
        ];
    }

    private function checkDuplicateDetails(array $details, ?int $siswaId = null, ?int $excludePendaftaranId = null): array
    {
        $errors = [];

        // 1. Within-form duplicates
        $seen = [];
        foreach ($details as $index => $detail) {
            $key = $detail['bidang_studi_id'] . '-' . $detail['level_kelas_id'];
            if (isset($seen[$key])) {
                $num = $index + 1;
                $origNum = $seen[$key] + 1;
                $errors[] = "Detail kursus #{$num} memiliki kombinasi Bidang Studi & Level Kelas yang sama dengan detail #{$origNum}.";
            } else {
                $seen[$key] = $index;
            }
        }

        // 2. Check against existing DB records for same siswa
        if ($siswaId && empty($errors)) {
            $query = DetailPendaftaran::where('siswa_id', $siswaId);
            if ($excludePendaftaranId) {
                $query->where('pendaftaran_id', '!=', $excludePendaftaranId);
            }
            $existing = $query->get(['bidang_studi_id', 'level_kelas_id']);

            foreach ($details as $index => $detail) {
                foreach ($existing as $ex) {
                    if ($ex->bidang_studi_id == $detail['bidang_studi_id'] && $ex->level_kelas_id == $detail['level_kelas_id']) {
                        $bsName = BidangStudi::find($detail['bidang_studi_id'])?->nama_bidang_studi ?? '-';
                        $lkName = LevelKelas::find($detail['level_kelas_id'])?->nama_level ?? '-';
                        $errors[] = "Siswa sudah terdaftar pada Bidang Studi \"{$bsName}\" dengan Level \"{$lkName}\".";
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    private function validationMessages(): array
    {
        return [
            'tanggal_pendaftaran.required'           => 'Tanggal pendaftaran wajib diisi.',
            'tanggal_pendaftaran.date_format'         => 'Format tanggal pendaftaran harus dd/mm/yyyy (contoh: 20/10/2000).',
            'siswa.nama_siswa.required'               => 'Nama lengkap siswa wajib diisi.',
            'siswa.nama_siswa.max'                    => 'Nama lengkap siswa maksimal 100 karakter.',
            'siswa.nik.required'                      => 'NIK siswa wajib diisi.',
            'siswa.nik.digits'                         => 'NIK siswa harus berjumlah 16 digit.',
            'siswa.nik.unique'                         => 'NIK siswa sudah terdaftar.',
            'siswa.tempat_lahir.required'              => 'Tempat lahir wajib diisi.',
            'siswa.tanggal_lahir.required'             => 'Tanggal lahir wajib diisi.',
            'siswa.tanggal_lahir.date_format'          => 'Format tanggal lahir harus dd/mm/yyyy (contoh: 20/10/2000).',
            'siswa.agama.required'                     => 'Agama wajib dipilih.',
            'siswa.agama.in'                           => 'Agama yang dipilih tidak valid.',
            'siswa.jenis_kelamin.required'             => 'Jenis kelamin wajib dipilih.',
            'siswa.jenis_kelamin.in'                   => 'Jenis kelamin yang dipilih tidak valid.',
            'siswa.alamat.required'                    => 'Alamat siswa wajib diisi.',
            'siswa.alamat.max'                         => 'Alamat siswa maksimal 255 karakter.',
            'siswa.no_telepon.required'                => 'No telepon siswa wajib diisi.',
            'siswa.no_telepon.digits_between'          => 'No telepon siswa harus di antara 5 sampai 15 digit.',
            'siswa.email.email'                        => 'Format email tidak valid.',
            'siswa.email.max'                          => 'Email maksimal 255 karakter.',
            'siswa.jenis_tinggal.required'             => 'Jenis tinggal wajib dipilih.',
            'siswa.jenis_tinggal.in'                   => 'Jenis tinggal yang dipilih tidak valid.',
            'siswa.jenis_tinggal_lainnya.required_if'  => 'Jenis tinggal lainnya wajib diisi jika memilih Lainnya.',
            'siswa.abk.required'                       => 'Kebutuhan khusus wajib dipilih.',
            'siswa.abk.in'                             => 'Kebutuhan khusus yang dipilih tidak valid.',
            'siswa.abk_lainnya.required_if'            => 'Kebutuhan khusus lainnya wajib diisi jika memilih Lainnya.',
            'siswa.pendidikan_terakhir.required'            => 'Pendidikan terakhir wajib dipilih.',
            'siswa.pendidikan_terakhir.in'                  => 'Pendidikan terakhir yang dipilih tidak valid.',
            'siswa.pendidikan_terakhir_lainnya.required_if' => 'Pendidikan terakhir lainnya wajib diisi jika memilih Lainnya.',
            'siswa.pekerjaan.required'                      => 'Pekerjaan wajib dipilih.',
            'siswa.pekerjaan.in'                            => 'Pekerjaan yang dipilih tidak valid.',
            'siswa.pekerjaan_lainnya.required_if'           => 'Pekerjaan lainnya wajib diisi jika memilih Lainnya.',
            'details.required'                              => 'Minimal harus ada 1 detail kursus.',
            'details.min'                             => 'Minimal harus ada 1 detail kursus.',
            'details.*.bidang_studi_id.required'      => 'Bidang studi wajib dipilih pada setiap detail.',
            'details.*.bidang_studi_id.exists'        => 'Bidang studi yang dipilih tidak valid.',
            'details.*.bidang_studi_custom.max'       => 'Bidang studi custom maksimal 255 karakter.',
            'details.*.level_kelas_id.required'       => 'Level kelas wajib dipilih pada setiap detail.',
            'details.*.level_kelas_id.exists'         => 'Level kelas yang dipilih tidak valid.',
            'details.*.kategori_kelas_id.required'    => 'Kategori kelas wajib dipilih pada setiap detail.',
            'details.*.kategori_kelas_id.exists'      => 'Kategori kelas yang dipilih tidak valid.',
            'details.*.harga_kursus.required'         => 'Harga kursus wajib diisi pada setiap detail.',
            'details.*.harga_kursus.integer'          => 'Harga kursus harus berupa angka.',
            'details.*.harga_kursus.min'              => 'Harga kursus tidak boleh kurang dari 0.',
            'details.*.diskon1.integer'               => 'Diskon 1 harus berupa angka.',
            'details.*.diskon1.min'                   => 'Diskon 1 minimal 0%.',
            'details.*.diskon1.max'                   => 'Diskon 1 maksimal 100%.',
            'details.*.diskon2.integer'               => 'Diskon 2 harus berupa angka.',
            'details.*.diskon2.min'                   => 'Diskon 2 minimal 0%.',
            'details.*.diskon2.max'                   => 'Diskon 2 maksimal 100%.',
            'details.*.total_harga.required'          => 'Total harga wajib diisi pada setiap detail.',
            'details.*.total_harga.integer'           => 'Total harga harus berupa angka.',
            'details.*.total_harga.min'               => 'Total harga tidak boleh kurang dari 0.',
            'upload_ktp.required'                      => 'File KTP wajib diupload.',
            'upload_ktp.mimes'                         => 'Format KTP harus JPG, JPEG, atau PDF.',
            'upload_ktp.max'                           => 'Ukuran file KTP maksimal 2MB.',
            'upload_kk.required'                       => 'File KK wajib diupload.',
            'upload_kk.mimes'                          => 'Format KK harus JPG, JPEG, atau PDF.',
            'upload_kk.max'                            => 'Ukuran file KK maksimal 2MB.',
            'bukti_pembayaran.mimes'                   => 'Format bukti pembayaran harus JPG, JPEG, atau PDF.',
            'bukti_pembayaran.max'                     => 'Ukuran file bukti pembayaran maksimal 2MB.',
        ];
    }

    private function sanitizeHiddenInputs(Request $request): void
    {
        // jenis_tinggal: hidden input diisi oleh toggleStatusLainnya()
        // Jika hidden kosong tapi select punya value, gunakan value select
        if (empty($request->input('jenis_tinggal')) && $request->filled('jenis_tinggal_select')) {
            $selectValue = $request->input('jenis_tinggal_select');
            if ($selectValue !== 'lainnya') {
                $request->merge(['jenis_tinggal' => $selectValue]);
            }
            $this->logPendaftaran('WARN', 'jenis_tinggal kosong, fallback ke select value', [
                'select_value' => $selectValue,
            ]);
        }

        // status_saat_ini: hidden input diisi oleh toggleStatusSaatIniLainnya()
        if (empty($request->input('status_saat_ini')) && $request->filled('status_saat_ini_select')) {
            $selectValue = $request->input('status_saat_ini_select');
            if ($selectValue !== 'lainnya') {
                $request->merge(['status_saat_ini' => $selectValue]);
            }
            $this->logPendaftaran('WARN', 'status_saat_ini kosong, fallback ke select value', [
                'select_value' => $selectValue,
            ]);
        }

        // id_bidang_studi: hidden input diisi oleh toggleBidangStudiLainnya()
        if (empty($request->input('id_bidang_studi')) && $request->filled('bidang_studi_select')) {
            $selectValue = $request->input('bidang_studi_select');
            $request->merge(['id_bidang_studi' => $selectValue]);
            $this->logPendaftaran('WARN', 'id_bidang_studi kosong, fallback ke select value', [
                'select_value' => $selectValue,
            ]);
        }

        // pendidikan_terakhir: langsung dari select, tapi bisa 'lainnya'
        // Jika pilih 'lainnya' tapi hidden kosong, fallback ke text 'lainnya'
        if ($request->input('pendidikan_terakhir') === 'lainnya' && empty($request->input('pendidikan_terakhir_hidden'))) {
            $this->logPendaftaran('WARN', 'pendidikan_terakhir lainnya tapi hidden kosong');
        }
    }

    /**
     * Parse tanggal lahir dari berbagai format yang mungkin dikirim browser.
     * Flatpickr menggunakan dd/mm/yyyy, tapi jika CDN gagal load, browser bisa
     * mengirim format native date input yang berbeda-beda per device.
     * 
     * @return string|null Y-m-d format atau null jika tidak bisa diparsing
     */
    private function parseTanggalLahir(?string $tanggalLahir): ?string
    {
        if (empty($tanggalLahir)) {
            return null;
        }

        $tanggalLahir = trim($tanggalLahir);

        // Format yang mungkin dikirim berbagai browser/device:
        $formats = [
            'd/m/Y',       // Flatpickr default: 26/02/2026
            'd-m-Y',       // Variasi dash: 26-02-2026
            'Y-m-d',       // HTML5 native date input: 2026-02-26
            'm/d/Y',       // US format (beberapa browser): 02/26/2026
            'd M Y',       // 26 Feb 2026
            'd F Y',       // 26 February 2026
            'Y/m/d',       // 2026/02/26
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $tanggalLahir);
                if ($date && $date->format($format) === $tanggalLahir) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Last resort: let Carbon try to parse it naturally
        try {
            $date = Carbon::parse($tanggalLahir);
            if ($date && $date->year > 1900 && $date->year < 2100) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // ignore
        }

        return null;
    }

    /**
     * Hapus file yang sudah diupload jika terjadi error
     */
    private function cleanupUploadedFiles(?string ...$paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                try {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                } catch (\Exception $e) {
                    $this->logPendaftaran('WARN', 'Gagal menghapus file saat cleanup', [
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Get shortened stack trace (top 5 frames) untuk logging
     */
    private function getShortTrace(\Throwable $e): string
    {
        $trace = explode("\n", $e->getTraceAsString());
        return implode("\n", array_slice($trace, 0, 5));
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'tempat_daftar',
            'bidang_studi_id',
            'tgl_mulai',
            'tgl_akhir',
        ]);

        $fileName = 'Laporan_Pendaftaran_Kursus_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new PendaftaranMultiSheetExport($filters), $fileName);
    }
}