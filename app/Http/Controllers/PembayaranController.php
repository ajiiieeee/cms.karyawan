<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\DetailPendaftaran;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PembayaranMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;

class PembayaranController extends Controller
{
    public function index(): View
    {
        return view('pembayaran.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'desc')) === 'desc' ? 'desc' : 'asc';

        // Mapped to DataTable column indices (0=No, 1=tanggal, 2=no_pendaftaran, 3=nama_siswa, 4=kursus)
        $sortableColumns = [
            0 => 'created_at',
            1 => 'tanggal_pembayaran',
            2 => 'created_at',
            3 => 'created_at',
            4 => 'created_at',
        ];
        $orderColumn = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = Pembayaran::count();

        $query = Pembayaran::with(['siswa', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas', 'pendaftaran']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sq) use ($search) {
                    $sq->where('nama_siswa', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                })
                    ->orWhereHas('pendaftaran', function ($sq) use ($search) {
                        $sq->where('no_pendaftaran', 'like', "%{$search}%");
                    })
                    ->orWhereHas('detailPendaftaran.bidangStudi', function ($sq) use ($search) {
                        $sq->where('nama_bidang_studi', 'like', "%{$search}%");
                    });
            });
        }

        $filteredRecords = $query->count();

        $pembayarans = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();

        $data = $pembayarans->map(function ($pembayaran) use (&$no, $currentUser) {
            $no++;
            $encId = IdEncryptor::encrypt($pembayaran->id);

            $showBtn = '';
            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('pembayaran', 'view')) {
                $showBtn = '<a href="' . route('pembayaran.show', $encId) . '" class="btn btn-sm btn-outline-info-600 d-inline-flex align-items-center gap-1"><i class="ri-eye-line text-sm"></i> Detail</a>';
            }

            if ($currentUser->hasMenuAccess('pembayaran', 'edit')) {
                $editBtn = '<a href="' . route('pembayaran.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('pembayaran', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($pembayaran->siswa->nama_siswa ?? '-') . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            $bidangStudi = $pembayaran->detailPendaftaran->bidangStudi->nama_bidang_studi ?? '-';
            $level = $pembayaran->detailPendaftaran->levelKelas->nama_level ?? '-';

            return [
                'no'                  => $no,
                'tanggal_pembayaran'  => e($pembayaran->tanggal_pembayaran->format('d/m/Y')),
                'no_pendaftaran'      => e($pembayaran->detailPendaftaran->no_pendaftaran ?? '-'),
                'nama_siswa'          => e($pembayaran->siswa->nama_siswa ?? '-'),
                'kursus'              => e($bidangStudi . ' - ' . $level),
                'status_pembayaran'   => $pembayaran->status_pembayaran
                    ? '<span class="badge bg-success-100 text-success-600 px-12 py-6 radius-4 fw-semibold text-xs">Lunas</span>'
                    : '<span class="badge bg-warning-100 text-warning-600 px-12 py-6 radius-4 fw-semibold text-xs">Pending</span>',
                'aksi'                => '<div class="d-inline-flex align-items-center gap-8">' . $showBtn . $editBtn . $deleteBtn . '</div>',
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
        return view('pembayaran.create');
    }

    public function searchKursus(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));

        $query = DetailPendaftaran::with(['pendaftaran', 'siswa', 'bidangStudi', 'levelKelas', 'kategoriKelas'])
            ->whereHas('pendaftaran')
            ->whereHas('siswa')
            ->whereDoesntHave('pembayaran'); // Hanya pendaftaran yang belum pernah ada pembayaran

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_pendaftaran', 'like', "%{$search}%")
                    ->orWhereHas('siswa', function ($sq) use ($search) {
                        $sq->where('nama_siswa', 'like', "%{$search}%");
                    })
                    ->orWhereHas('bidangStudi', function ($sq) use ($search) {
                        $sq->where('nama_bidang_studi', 'like', "%{$search}%");
                    });
            });
        }

        $results = $query->limit(20)->get()->map(function ($detail) {
            $noPendaftaran = $detail->no_pendaftaran ?? '-';
            $namaSiswa = $detail->siswa->nama_siswa ?? '-';
            $bidangStudi = $detail->bidangStudi->nama_bidang_studi ?? '-';
            $level = $detail->levelKelas->nama_level ?? '-';
            $totalHarga = $detail->total_harga;

            return [
                'id'               => $detail->id,
                'text'             => $noPendaftaran . ' - ' . $namaSiswa . ' - ' . $bidangStudi . ' - ' . $level,
                'pendaftaran_id'   => $detail->pendaftaran_id,
                'siswa_id'         => $detail->siswa_id,
                'nama_siswa'       => $namaSiswa,
                'nik'              => $detail->siswa->nik ?? '-',
                'no_telepon'       => $detail->siswa->no_telepon ?? '-',
                'email'            => $detail->siswa->email ?? '-',
                'alamat'           => $detail->siswa->alamat ?? '-',
                'no_pendaftaran'   => $noPendaftaran,
                'bidang_studi'     => $bidangStudi,
                'level_kelas'      => $level,
                'kategori_kelas'   => $detail->kategoriKelas->nama_kategori ?? '-',
                'total_harga'      => $totalHarga,
                'total_harga_fmt'  => 'Rp ' . number_format($totalHarga, 0, ',', '.'),
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Determine payment type from frontend
        $paymentType = $request->input('payment_type');

        // Get validation rules based on payment type
        $rules = $this->getValidationRulesForPayment('store', $paymentType);

        $validated = $request->validate($rules, $this->validationMessages());

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;

            $detail = DetailPendaftaran::with(['pendaftaran', 'siswa', 'bidangStudi', 'levelKelas'])
                ->findOrFail($validated['detail_pendaftaran_id']);

            // Pastikan detail_pendaftaran belum memiliki data pembayaran
            if (Pembayaran::where('detail_pendaftaran_id', $detail->id)->exists()) {
                DB::rollBack();
                return redirect()->back()->withInput()
                    ->with('error', 'Pendaftaran kursus ini sudah memiliki data pembayaran. Silakan edit data pembayaran yang sudah ada.');
            }

            $jumlahTagihan = $detail->total_harga;

            // Pastikan jika uang_muka/pelunasan kosong, nilainya adalah 0 (bukan null)
            $uangMuka = isset($validated['uang_muka']) && $validated['uang_muka'] > 0 ? (int)$validated['uang_muka'] : 0;
            $pelunasan = isset($validated['pelunasan']) && $validated['pelunasan'] > 0 ? (int)$validated['pelunasan'] : 0;

            // Jika pilih pelunasan_only dan pelunasan diisi seharga total tagihan, set pelunasan = total tagihan
            if ($paymentType === 'pelunasan_only' && $pelunasan <= 0) {
                $pelunasan = $jumlahTagihan;
            }

            $sisaTagihan = $jumlahTagihan - $uangMuka - $pelunasan;
            if ($sisaTagihan < 0) {
                $sisaTagihan = 0;
            }

            $tanggalPembayaran = Carbon::createFromFormat('d/m/Y', $validated['tanggal_pembayaran']);
            $tempat = $validated['tempat_pembayaran'];

            $noKwitansiDp = $uangMuka > 0 ? $this->generateNoKwitansi($tempat) : null;
            $noKwitansiPelunasan = $pelunasan > 0 ? $this->generateNoKwitansi($tempat) : null;

            // Generate filename components
            $namaSiswa   = Str::slug($detail->siswa->nama_siswa ?? 'siswa');
            $bidangStudi = Str::slug($detail->bidangStudi->nama_bidang_studi ?? 'bidang-studi');
            $level       = Str::slug($detail->levelKelas->nama_level ?? 'level');

            $buktiDpPath = null;
            if ($request->hasFile('bukti_uang_muka')) {
                $ext = $request->file('bukti_uang_muka')->getClientOriginalExtension();
                $filename = "DP-{$namaSiswa}-{$bidangStudi}-{$level}.{$ext}";
                $buktiDpPath = $request->file('bukti_uang_muka')->storeAs('pembayaran/bukti', $filename, 'public');
            }

            $buktiPelunasanPath = null;
            if ($request->hasFile('bukti_pelunasan')) {
                $ext = $request->file('bukti_pelunasan')->getClientOriginalExtension();
                $filename = "LUNAS-{$namaSiswa}-{$bidangStudi}-{$level}.{$ext}";
                $buktiPelunasanPath = $request->file('bukti_pelunasan')->storeAs('pembayaran/bukti', $filename, 'public');
            }

            $pembayaran = Pembayaran::create([
                'detail_pendaftaran_id' => $detail->id,
                'pendaftaran_id'        => $detail->pendaftaran_id,
                'siswa_id'              => $detail->siswa_id,
                'jumlah_tagihan'        => $jumlahTagihan,
                'tanggal_pembayaran'    => $tanggalPembayaran->toDateString(),
                'tanggal_pelunasan'     => $pelunasan > 0 ? $tanggalPembayaran->toDateString() : null,
                'jenis_pembayaran'      => $validated['jenis_pembayaran'],
                'bank_tujuan'           => $validated['bank_tujuan'] ?? null,
                'uang_muka'             => $uangMuka, // Simpan 0 (bukan null) jika tanpa DP
                'pelunasan'             => $pelunasan,
                'tempat_pembayaran'     => $tempat,
                'sisa_tagihan'          => $sisaTagihan,
                'status_pembayaran'     => $sisaTagihan == 0 ? 1 : 0,
                'no_kwitansi_dp'        => $noKwitansiDp,
                'no_kwitansi_pelunasan' => $noKwitansiPelunasan,
                'bukti_uang_muka'       => $buktiDpPath,
                'bukti_pelunasan'       => $buktiPelunasanPath,
                'created_by'            => $userName,
                'updated_by'            => $userName,
            ]);

            $keteranganDefault = 'Pembayaran awal ' . ($uangMuka > 0 ? 'DP' : 'Pelunasan');
            $catatanInput = $request->input('catatan');

            // Catat ke riwayat pembayaran
            \App\Models\RiwayatPembayaran::create([
                'pembayaran_id'     => $pembayaran->id,
                'no_kwitansi'       => $uangMuka > 0 ? $noKwitansiDp : $noKwitansiPelunasan,
                'jenis_transaksi'   => $uangMuka > 0 ? 'dp' : 'pelunasan',
                'jumlah_bayar'      => $uangMuka > 0 ? $uangMuka : $pelunasan,
                'tanggal_bayar'     => $tanggalPembayaran->toDateString(),
                'metode_pembayaran' => $validated['jenis_pembayaran'],
                'tempat_pembayaran' => $tempat,
                'catatan'           => !empty($catatanInput) ? $catatanInput : $keteranganDefault,
                'created_by'        => $userName,
            ]);

            DB::commit();
            return redirect()->route('pembayaran.index')
                ->with('success', 'Data pembayaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Pembayaran store error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data pembayaran: ' . $e->getMessage());
        }
    }

    public function show(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::with(['siswa', 'pendaftaran', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas', 'detailPendaftaran.kategoriKelas', 'riwayat'])->findOrFail($id);

        return view('pembayaran.show', compact('pembayaran'));
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::with(['siswa', 'pendaftaran', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas', 'detailPendaftaran.kategoriKelas'])->findOrFail($id);

        return view('pembayaran.edit', compact('pembayaran'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        $updateType = $request->input('update_type'); // update_dp | add_pelunasan | update_pelunasan
        $rules = $this->getValidationRulesForPayment('update', $updateType, $pembayaran);

        $validated = $request->validate($rules, $this->validationMessages());

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;
            $detail = DetailPendaftaran::with(['pendaftaran', 'siswa', 'bidangStudi', 'levelKelas'])->findOrFail($validated['detail_pendaftaran_id']);

            $jumlahTagihan = $detail->total_harga;

            // Tentukan nilai uang muka & pelunasan
            if ($updateType === 'update_dp') {
                $uangMuka  = $validated['uang_muka'] ?? 0;
                $pelunasan = $pembayaran->pelunasan ?? 0;
            } else {
                $uangMuka  = $pembayaran->uang_muka ?? 0;
                $pelunasan = $validated['pelunasan'] ?? 0;
            }

            $sisaTagihan = $jumlahTagihan - $uangMuka - $pelunasan;
            if ($sisaTagihan < 0) {
                $sisaTagihan = 0;
            }

            $tanggalInput = Carbon::createFromFormat('d/m/Y', $validated['tanggal_pembayaran']);
            $tempat = $validated['tempat_pembayaran'];
            $bankTujuan = ($validated['jenis_pembayaran'] === 'transfer') ? ($request->bank_tujuan ?? null) : null;


            // Jika add pelunasan -> tanggal_pembayaran TETAP (tanggal DP lama), tanggal_pelunasan memakai tanggal baru.
            if ($updateType === 'update_dp') {
                $tglDp = $tanggalInput->toDateString();
                $tglPelunasan = $pembayaran->tanggal_pelunasan ? $pembayaran->tanggal_pelunasan->toDateString() : null;
            } elseif ($updateType === 'add_pelunasan') {
                $tglDp = $pembayaran->tanggal_pembayaran ? $pembayaran->tanggal_pembayaran->toDateString() : $tanggalInput->toDateString();
                $tglPelunasan = $tanggalInput->toDateString();
            } else { // update_pelunasan
                $tglDp = $pembayaran->tanggal_pembayaran ? $pembayaran->tanggal_pembayaran->toDateString() : $tanggalInput->toDateString();
                $tglPelunasan = $tanggalInput->toDateString();
            }

            $updateData = [
                'detail_pendaftaran_id' => $detail->id,
                'pendaftaran_id'        => $detail->pendaftaran_id,
                'siswa_id'              => $detail->siswa_id,
                'jumlah_tagihan'        => $jumlahTagihan,
                'tanggal_pembayaran'    => $tglDp, // Mengunci tanggal DP agar tidak tertimpa
                'tanggal_pelunasan'     => $tglPelunasan, // Menyimpan tanggal pelunasan secara terpisah
                'jenis_pembayaran'      => $validated['jenis_pembayaran'],
                'bank_tujuan'           => $bankTujuan,
                'uang_muka'             => $uangMuka > 0 ? $uangMuka : null,
                'pelunasan'             => $pelunasan > 0 ? $pelunasan : null,
                'tempat_pembayaran'     => $tempat,
                'sisa_tagihan'          => $sisaTagihan,
                'status_pembayaran'     => $sisaTagihan == 0 ? 1 : 0,
                'updated_by'            => $userName,
            ];

            // Generate nomor kwitansi pelunasan jika belum ada
            if (in_array($updateType, ['add_pelunasan', 'update_pelunasan']) && !$pembayaran->no_kwitansi_pelunasan) {
                $updateData['no_kwitansi_pelunasan'] = $this->generateNoKwitansi($tempat);
            }

            // Upload bukti jika ada
            $namaSiswa   = Str::slug($detail->siswa->nama_siswa ?? 'siswa');
            $bidangStudi = Str::slug($detail->bidangStudi->nama_bidang_studi ?? 'bidang-studi');
            $level       = Str::slug($detail->levelKelas->nama_level ?? 'level');

            if ($updateType === 'update_dp' && $request->hasFile('bukti_uang_muka')) {
                if ($pembayaran->bukti_uang_muka) {
                    Storage::disk('public')->delete($pembayaran->bukti_uang_muka);
                }
                $ext = $request->file('bukti_uang_muka')->getClientOriginalExtension();
                $filename = "DP-{$namaSiswa}-{$bidangStudi}-{$level}.{$ext}";
                $updateData['bukti_uang_muka'] = $request->file('bukti_uang_muka')->storeAs('pembayaran/bukti', $filename, 'public');
            }

            if (in_array($updateType, ['add_pelunasan', 'update_pelunasan']) && $request->hasFile('bukti_pelunasan')) {
                if ($pembayaran->bukti_pelunasan) {
                    Storage::disk('public')->delete($pembayaran->bukti_pelunasan);
                }
                $ext = $request->file('bukti_pelunasan')->getClientOriginalExtension();
                $filename = "LUNAS-{$namaSiswa}-{$bidangStudi}-{$level}.{$ext}";
                $updateData['bukti_pelunasan'] = $request->file('bukti_pelunasan')->storeAs('pembayaran/bukti', $filename, 'public');
            }

            $pembayaran->update($updateData);

            // Update / Insert ke Tabel Riwayat
            if ($updateType === 'update_dp') {
                \App\Models\RiwayatPembayaran::where('pembayaran_id', $pembayaran->id)
                    ->where('jenis_transaksi', 'dp')
                    ->update([
                        'jumlah_bayar'      => $uangMuka,
                        'tanggal_bayar'     => $tglDp,
                        'metode_pembayaran' => $validated['jenis_pembayaran'],
                        'tempat_pembayaran' => $tempat,
                        'catatan'           => $request->input('catatan'),
                    ]);
            } elseif ($updateType === 'add_pelunasan') {
                \App\Models\RiwayatPembayaran::create([
                    'pembayaran_id'     => $pembayaran->id,
                    'no_kwitansi'       => $pembayaran->no_kwitansi_pelunasan ?? $updateData['no_kwitansi_pelunasan'],
                    'jenis_transaksi'   => 'pelunasan',
                    'jumlah_bayar'      => $pelunasan,
                    'tanggal_bayar'     => $tglPelunasan,
                    'metode_pembayaran' => $validated['jenis_pembayaran'],
                    'tempat_pembayaran' => $tempat,
                    'catatan'           => $request->input('catatan') ?? 'Pelunasan pembayaran',
                    'created_by'        => $userName,
                ]);
            } elseif ($updateType === 'update_pelunasan') {
                \App\Models\RiwayatPembayaran::where('pembayaran_id', $pembayaran->id)
                    ->where('jenis_transaksi', 'pelunasan')
                    ->update([
                        'jumlah_bayar'      => $pelunasan,
                        'tanggal_bayar'     => $tglPelunasan,
                        'metode_pembayaran' => $validated['jenis_pembayaran'],
                        'tempat_pembayaran' => $tempat,
                        'catatan'           => $request->input('catatan'),
                    ]);
            }

            DB::commit();
            return redirect()->route('pembayaran.index')->with('success', 'Data pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Pembayaran update error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data pembayaran.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::with('penjadwalan')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Hapus file GBMP penjadwalan terkait (cascade FK akan hapus row-nya)
            if ($pembayaran->penjadwalan && $pembayaran->penjadwalan->upload_gbmp) {
                Storage::disk('public')->delete($pembayaran->penjadwalan->upload_gbmp);
            }

            // Hapus file bukti pembayaran
            if ($pembayaran->bukti_uang_muka) {
                Storage::disk('public')->delete($pembayaran->bukti_uang_muka);
            }
            if ($pembayaran->bukti_pelunasan) {
                Storage::disk('public')->delete($pembayaran->bukti_pelunasan);
            }

            $pembayaran->delete();
            DB::commit();
            return redirect()->route('pembayaran.index')
                ->with('success', 'Data pembayaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data pembayaran.');
        }
    }

    private function formatMetodePembayaran($riwayat, $bankTujuan = null): string
    {
        if (!$riwayat) {
            return 'Tunai';
        }

        $metode = strtolower($riwayat->metode_pembayaran ?? 'tunai');

        if ($metode === 'transfer') {
            $daftarRekening = [
                'BCA'        => '829 1478 000',
                'Mandiri'    => '1410 0150 34564',
                'Bank Jatim' => '065 100 4268',
            ];

            $bank = $bankTujuan ?? 'BCA';
            $noRek = $daftarRekening[$bank] ?? '829 1478 000';

            return "Transfer Bank {$bank} (Rek: {$noRek} a.n Creative Media)";
        }

        return 'Tunai';
    }

    public function kwitansiDp(string $encryptedId)
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::with(['siswa', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas', 'detailPendaftaran.kategoriKelas', 'riwayat'])->findOrFail($id);

        if (!$pembayaran->no_kwitansi_dp || $pembayaran->uang_muka <= 0) {
            return redirect()->back()->with('error', 'Kwitansi uang muka tidak tersedia.');
        }

        // Ambil riwayat khusus DP
        $riwayatDp = $pembayaran->riwayat->where('jenis_transaksi', 'dp')->first();
        $tglBayarDp = $riwayatDp ? $riwayatDp->tanggal_bayar : $pembayaran->tanggal_pembayaran;

        $data = [
            'pembayaran'        => $pembayaran,
            'no_kwitansi'       => $pembayaran->no_kwitansi_dp,
            'nama_siswa'        => $pembayaran->siswa->nama_siswa ?? '-',
            'nominal'           => $pembayaran->uang_muka,
            'terbilang'         => $this->terbilang($pembayaran->uang_muka) . ' Rupiah',
            'keterangan'        => 'Pembayaran uang muka Bidang Studi ' . ($pembayaran->detailPendaftaran->bidangStudi->nama_bidang_studi ?? '-') . ' ' . ($pembayaran->detailPendaftaran->kategoriKelas->nama_kategori ?? '') . ' a.n ' . strtoupper($pembayaran->siswa->nama_siswa ?? '-'),
            'metode_pembayaran' => $this->formatMetodePembayaran($riwayatDp, $pembayaran->bank_tujuan),
            'tanggal'           => \Carbon\Carbon::parse($tglBayarDp)->locale('id')->translatedFormat('d F Y'),
            'tanggal_cetak'     => now()->format('d-m-Y'),
        ];

        $pdf = Pdf::loadView('pembayaran.kwitansi', $data);
        $pdf->setPaper('a5', 'landscape');

        return $pdf->download('Kwitansi-DP-' . str_replace('/', '-', $pembayaran->no_kwitansi_dp) . '.pdf');
    }

    public function kwitansiPelunasan(string $encryptedId)
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::with(['siswa', 'detailPendaftaran.bidangStudi', 'detailPendaftaran.levelKelas', 'detailPendaftaran.kategoriKelas', 'riwayat'])->findOrFail($id);

        if (!$pembayaran->no_kwitansi_pelunasan || !$pembayaran->pelunasan || $pembayaran->pelunasan <= 0) {
            return redirect()->back()->with('error', 'Kwitansi pelunasan tidak tersedia.');
        }

        // Ambil riwayat khusus Pelunasan
        $riwayatPelunasan = $pembayaran->riwayat->where('jenis_transaksi', 'pelunasan')->first();
        $tglBayarPelunasan = $riwayatPelunasan
            ? $riwayatPelunasan->tanggal_bayar
            : ($pembayaran->tanggal_pelunasan ?? $pembayaran->tanggal_pembayaran);

        $data = [
            'pembayaran'        => $pembayaran,
            'no_kwitansi'       => $pembayaran->no_kwitansi_pelunasan,
            'nama_siswa'        => $pembayaran->siswa->nama_siswa ?? '-',
            'nominal'           => $pembayaran->pelunasan,
            'terbilang'         => $this->terbilang($pembayaran->pelunasan) . ' Rupiah',
            'keterangan'        => 'Pembayaran pelunasan Bidang Studi ' . ($pembayaran->detailPendaftaran->bidangStudi->nama_bidang_studi ?? '-') . ' ' . ($pembayaran->detailPendaftaran->kategoriKelas->nama_kategori ?? '') . ' a.n ' . strtoupper($pembayaran->siswa->nama_siswa ?? '-'),
            'metode_pembayaran' => $this->formatMetodePembayaran($riwayatPelunasan, $pembayaran->bank_tujuan),
            'tanggal'           => \Carbon\Carbon::parse($tglBayarPelunasan)->locale('id')->translatedFormat('d F Y'),
            'tanggal_cetak'     => now()->format('d-m-Y'),
        ];

        $pdf = Pdf::loadView('pembayaran.kwitansi', $data);
        $pdf->setPaper('a5', 'landscape');

        return $pdf->download('Kwitansi-Pelunasan-' . str_replace('/', '-', $pembayaran->no_kwitansi_pelunasan) . '.pdf');
    }

    private function generateNoKwitansi(string $tempat): string
    {
        $year = date('Y');
        $tempatCode = $tempat === 'Nginden' ? 'NGD' : 'TBN';
        $suffix = '/CM/' . $year;

        $maxDp = Pembayaran::where('no_kwitansi_dp', 'like', '%' . $suffix)
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(no_kwitansi_dp, '/', 1) AS UNSIGNED)) as max_num")
            ->value('max_num') ?? 0;

        $maxPel = Pembayaran::where('no_kwitansi_pelunasan', 'like', '%' . $suffix)
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(no_kwitansi_pelunasan, '/', 1) AS UNSIGNED)) as max_num")
            ->value('max_num') ?? 0;

        $nextNum = max($maxDp, $maxPel) + 1;

        return str_pad($nextNum, 4, '0', STR_PAD_LEFT) . '/KP/' . $tempatCode . $suffix;
    }

    private function terbilang(int $angka): string
    {
        $angka = abs($angka);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return $huruf[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            return $this->terbilang(intdiv($angka, 10)) . ' Puluh' . ($angka % 10 > 0 ? ' ' . $this->terbilang($angka % 10) : '');
        } elseif ($angka < 200) {
            return 'Seratus' . ($angka - 100 > 0 ? ' ' . $this->terbilang($angka - 100) : '');
        } elseif ($angka < 1000) {
            return $this->terbilang(intdiv($angka, 100)) . ' Ratus' . ($angka % 100 > 0 ? ' ' . $this->terbilang($angka % 100) : '');
        } elseif ($angka < 2000) {
            return 'Seribu' . ($angka - 1000 > 0 ? ' ' . $this->terbilang($angka - 1000) : '');
        } elseif ($angka < 1000000) {
            return $this->terbilang(intdiv($angka, 1000)) . ' Ribu' . ($angka % 1000 > 0 ? ' ' . $this->terbilang($angka % 1000) : '');
        } elseif ($angka < 1000000000) {
            return $this->terbilang(intdiv($angka, 1000000)) . ' Juta' . ($angka % 1000000 > 0 ? ' ' . $this->terbilang($angka % 1000000) : '');
        } elseif ($angka < 1000000000000) {
            return $this->terbilang(intdiv($angka, 1000000000)) . ' Miliar' . ($angka % 1000000000 > 0 ? ' ' . $this->terbilang($angka % 1000000000) : '');
        }

        return '';
    }

    private function getValidationRulesForPayment($action, $paymentType, $pembayaran = null): array
    {
        $baseRules = [
            'detail_pendaftaran_id' => ['required', 'integer', 'exists:detail_pendaftaran,id'],
            'tanggal_pembayaran'    => ['required', 'date_format:d/m/Y'],
            'jenis_pembayaran'      => ['required', Rule::in(['tunai', 'transfer'])],
            'bank_tujuan'           => ['required_if:jenis_pembayaran,transfer', 'nullable', 'string', 'max:50'],
            'tempat_pembayaran'     => ['required', Rule::in(['Tubanan', 'Nginden'])],
            'catatan'               => ['nullable', 'string', 'max:255'],
        ];


        // Definisi aturan file bukti dibuat opsional (nullable) maksimal 2 MB
        $buktiFileRules = ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:2048'];

        if ($action === 'store') {
            // CREATE ACTION
            if ($paymentType === 'dp_only') {
                return array_merge($baseRules, [
                    'payment_type'      => ['required', 'in:dp_only'],
                    'uang_muka'         => ['required', 'integer', 'min:1'],
                    'bukti_uang_muka'   => $buktiFileRules, // Opsional
                    'pelunasan'         => ['nullable', 'integer', 'min:0'],
                    'bukti_pelunasan'   => ['nullable'],
                ]);
            } elseif ($paymentType === 'pelunasan_only') {
                return array_merge($baseRules, [
                    'payment_type'      => ['required', 'in:pelunasan_only'],
                    'uang_muka'         => ['nullable', 'integer', 'min:0'],
                    'bukti_uang_muka'   => ['nullable'],
                    'pelunasan'         => ['required', 'integer', 'min:1'],
                    'bukti_pelunasan'   => $buktiFileRules, // Opsional
                ]);
            }
        } elseif ($action === 'update') {
            // UPDATE ACTION
            $updateTypeRules = ['required', Rule::in(['update_dp', 'add_pelunasan', 'update_pelunasan'])];

            if ($paymentType === 'update_dp') {
                return array_merge($baseRules, [
                    'update_type'     => $updateTypeRules,
                    'uang_muka'       => ['required', 'integer', 'min:1'],
                    'bukti_uang_muka' => $buktiFileRules, // Opsional
                    'pelunasan'       => ['nullable', 'integer', 'min:0'],
                    'bukti_pelunasan' => ['nullable'],
                ]);
            } elseif ($paymentType === 'add_pelunasan' || $paymentType === 'update_pelunasan') {
                return array_merge($baseRules, [
                    'update_type'     => $updateTypeRules,
                    'uang_muka'       => ['nullable', 'integer', 'min:0'],
                    'bukti_uang_muka' => ['nullable'],
                    'pelunasan'       => ['required', 'integer', 'min:1'],
                    'bukti_pelunasan' => $buktiFileRules, // Opsional
                ]);
            }

            // Fallback: only validate update_type
            return array_merge($baseRules, ['update_type' => $updateTypeRules]);
        }

        return $baseRules;
    }

    private function validationMessages(): array
    {
        return [
            'detail_pendaftaran_id.required' => 'Pendaftaran kursus wajib dipilih.',
            'detail_pendaftaran_id.integer'  => 'Pendaftaran kursus tidak valid.',
            'detail_pendaftaran_id.exists'   => 'Pendaftaran kursus yang dipilih tidak ditemukan.',
            'tanggal_pembayaran.required'    => 'Tanggal pembayaran wajib diisi.',
            'tanggal_pembayaran.date_format' => 'Format tanggal pembayaran harus dd/mm/yyyy (contoh: 20/10/2000).',
            'jenis_pembayaran.required'      => 'Jenis pembayaran wajib dipilih.',
            'jenis_pembayaran.in'            => 'Jenis pembayaran yang dipilih tidak valid. Pilih Tunai atau Transfer.',
            'uang_muka.required'             => 'Uang muka wajib diisi.',
            'uang_muka.integer'              => 'Uang muka harus berupa angka.',
            'uang_muka.min'                  => 'Uang muka tidak boleh kosong.',
            'pelunasan.required'             => 'Pelunasan wajib diisi.',
            'pelunasan.integer'              => 'Pelunasan harus berupa angka.',
            'pelunasan.min'                  => 'Pelunasan tidak boleh kosong.',
            'tempat_pembayaran.required'     => 'Tempat pembayaran wajib dipilih.',
            'tempat_pembayaran.in'           => 'Tempat pembayaran yang dipilih tidak valid. Pilih Tubanan atau Nginden.',
            'payment_type.required'          => 'Jenis pembayaran wajib dipilih.',
            'payment_type.in'                => 'Jenis pembayaran yang dipilih tidak valid.',
            'update_type.required'           => 'Pilihan edit wajib dipilih.',
            'update_type.in'                 => 'Pilihan edit yang dipilih tidak valid.',
            'bukti_uang_muka.file'           => 'Bukti uang muka harus berupa file.',
            'bukti_uang_muka.mimes'          => 'Bukti uang muka harus berformat PDF, JPG, atau JPEG.',
            'bukti_uang_muka.max'            => 'Bukti pembayaran uang muka maksimal 2 MB.',
            'bukti_uang_muka.required'       => 'Bukti uang muka wajib diunggah.',
            'bukti_pelunasan.file'           => 'Bukti pelunasan harus berupa file.',
            'bukti_pelunasan.mimes'          => 'Bukti pelunasan harus berformat PDF, JPG, atau JPEG.',
            'bukti_pelunasan.max'            => 'Bukti pembayaran pelunasan maksimal 2 MB.',
            'bukti_pelunasan.required'       => 'Bukti pelunasan wajib diunggah.',
        ];
    }

    public function storeRiwayat(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        $validated = $request->validate([
            'jenis_transaksi'   => ['required', Rule::in(['dp', 'cicilan', 'pelunasan'])],
            'jumlah_bayar'      => ['required', 'integer', 'min:1', 'max:' . $pembayaran->sisa_tagihan],
            'tanggal_bayar'     => ['required', 'date_format:d/m/Y'],
            'metode_pembayaran' => ['required', Rule::in(['tunai', 'transfer'])],
            'tempat_pembayaran' => ['required', Rule::in(['Tubanan', 'Nginden'])],
            'catatan'           => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $userName = Auth::user()->nama ?? Auth::user()->username;
            $tanggalBayar = Carbon::createFromFormat('d/m/Y', $validated['tanggal_bayar']);
            $noKwitansi = $this->generateNoKwitansi($validated['tempat_pembayaran']);

            // 1. Simpan Mutasi Pembayaran Baru
            \App\Models\RiwayatPembayaran::create([
                'pembayaran_id'     => $pembayaran->id,
                'no_kwitansi'       => $noKwitansi,
                'jenis_transaksi'   => $validated['jenis_transaksi'],
                'jumlah_bayar'      => $validated['jumlah_bayar'],
                'tanggal_bayar'     => $tanggalBayar->toDateString(),
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'tempat_pembayaran' => $validated['tempat_pembayaran'],
                'catatan'           => $validated['catatan'],
                'created_by'        => $userName,
            ]);

            // 2. Hitung Sisa Tagihan Baru
            $sisaTagihanBaru = $pembayaran->sisa_tagihan - $validated['jumlah_bayar'];

            $updateData = [
                'sisa_tagihan'      => max(0, $sisaTagihanBaru),
                'status_pembayaran' => $sisaTagihanBaru <= 0 ? 1 : 0,
                'updated_by'        => $userName,
            ];

            if ($validated['jenis_transaksi'] === 'dp') {
                $updateData['uang_muka'] = ($pembayaran->uang_muka ?? 0) + $validated['jumlah_bayar'];
                $updateData['no_kwitansi_dp'] = $noKwitansi;
            } else {
                $updateData['pelunasan'] = ($pembayaran->pelunasan ?? 0) + $validated['jumlah_bayar'];
                $updateData['no_kwitansi_pelunasan'] = $noKwitansi;
                if ($sisaTagihanBaru <= 0) {
                    $updateData['tanggal_pelunasan'] = $tanggalBayar->toDateString();
                }
            }

            $pembayaran->update($updateData);

            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pembayaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Riwayat Pembayaran store error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan riwayat pembayaran.');
        }
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'status_pembayaran',
            'tempat_pembayaran',
            'jenis_pembayaran',
            'tgl_mulai',
            'tgl_akhir',
        ]);

        $fileName = 'Laporan_Pembayaran_Kursus_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new PembayaranMultiSheetExport($filters), $fileName);
    }
}
