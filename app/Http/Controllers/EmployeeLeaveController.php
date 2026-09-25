<?php

namespace App\Http\Controllers;

use App\Models\KategoriCuti;
use App\Models\PengajuanCuti;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmployeeLeaveController extends EmployeeController
{
    public function index(): View
    {
        $employee = $this->employee();
        $requests = PengajuanCuti::with('kategoriCuti')
            ->where('karyawan_id', $employee->id)
            ->latest('id')
            ->get();

        $categories = KategoriCuti::orderBy('nama_kategori')->get();
        $stats = $this->calculateLeaveStats($employee, $requests);

        return view('leave-requests.index', array_merge([
            'requests' => $requests,
            'categories' => $categories,
            'karyawan' => $employee,
            'requestItem' => new PengajuanCuti(),
        ], $stats));
    }

    public function create(): View
    {
        $employee = $this->employee();
        $requests = PengajuanCuti::where('karyawan_id', $employee->id)->get();
        $stats = $this->calculateLeaveStats($employee, $requests);

        return view('leave-requests.form', array_merge([
            'requestItem' => new PengajuanCuti(),
            'categories' => KategoriCuti::orderBy('nama_kategori')->get(),
            'karyawan' => $employee,
        ], $stats));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'jenis_cuti' => 'required|exists:kategori_cuti,id',
            'keterangan' => 'nullable|string|max:1000',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $days = Carbon::parse($data['tanggal_awal'])->diffInDays(Carbon::parse($data['tanggal_akhir'])) + 1;
        $employee = $this->employee();

        $createData = [
            'karyawan_id' => $employee->id,
            'tanggal_awal' => $data['tanggal_awal'],
            'tanggal_akhir' => $data['tanggal_akhir'],
            'jenis_cuti' => $data['jenis_cuti'],
            'jumlah_hari' => (string)$days,
            'keterangan' => $data['keterangan'] ?? null,
            'status_approval' => 0,
            'status_pengajuan' => 'Pending',
            'created_date' => now()->toDateString(),
            'created_by' => auth()->user()->nama,
        ];

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = 'cuti_' . $employee->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/cuti');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            if (Schema::hasColumn('pengajuan_cuti', 'lampiran')) {
                $createData['lampiran'] = 'uploads/cuti/' . $filename;
            }
        }

        PengajuanCuti::create($createData);

        return redirect()->route('leave-requests.index')->with('success', 'Permohonan cuti berhasil dikirim.');
    }

    public function show(PengajuanCuti $leave_request): View
    {
        $this->owned($leave_request);
        $leave_request->load('kategoriCuti', 'karyawan');
        $employee = $this->employee();
        $stats = $this->calculateLeaveStats($employee);

        return view('leave-requests.show', array_merge([
            'leaveRequest' => $leave_request,
            'karyawan' => $employee,
        ], $stats));
    }

    public function edit(PengajuanCuti $leave_request): View
    {
        $this->owned($leave_request);
        abort_unless($leave_request->status_pengajuan === 'Pending', 403);

        $employee = $this->employee();
        $requests = PengajuanCuti::where('karyawan_id', $employee->id)->get();
        $stats = $this->calculateLeaveStats($employee, $requests);

        return view('leave-requests.form', array_merge([
            'requestItem' => $leave_request,
            'categories' => KategoriCuti::orderBy('nama_kategori')->get(),
            'karyawan' => $employee,
        ], $stats));
    }

    public function update(Request $request, PengajuanCuti $leave_request): RedirectResponse
    {
        $this->owned($leave_request);
        abort_unless($leave_request->status_pengajuan === 'Pending', 403);

        $data = $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'jenis_cuti' => 'required|exists:kategori_cuti,id',
            'keterangan' => 'nullable|string|max:1000',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $days = Carbon::parse($data['tanggal_awal'])->diffInDays(Carbon::parse($data['tanggal_akhir'])) + 1;

        $updateData = [
            'tanggal_awal' => $data['tanggal_awal'],
            'tanggal_akhir' => $data['tanggal_akhir'],
            'jenis_cuti' => $data['jenis_cuti'],
            'jumlah_hari' => (string)$days,
            'keterangan' => $data['keterangan'] ?? null,
            'updated_date' => now()->toDateString(),
            'updated_by' => auth()->user()->nama,
        ];

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = 'cuti_' . $this->employee()->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/cuti');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            if (Schema::hasColumn('pengajuan_cuti', 'lampiran')) {
                $updateData['lampiran'] = 'uploads/cuti/' . $filename;
            }
        }

        $leave_request->update($updateData);

        return redirect()->route('leave-requests.index')->with('success', 'Permohonan cuti diperbarui.');
    }

    public function destroy(PengajuanCuti $leave_request): RedirectResponse
    {
        $this->owned($leave_request);
        abort_unless($leave_request->status_pengajuan === 'Pending', 403);
        $leave_request->delete();
        return back()->with('success', 'Permohonan cuti dihapus.');
    }

    private function owned(PengajuanCuti $item): void
    {
        abort_unless($item->karyawan_id === $this->employee()->id, 404);
    }

    private function calculateLeaveStats(Karyawan $employee, $requests = null): array
    {
        if ($requests === null) {
            $requests = PengajuanCuti::where('karyawan_id', $employee->id)->get();
        }

        $totalPengajuan = $requests->count();
        $totalMenunggu = $requests->where('status_pengajuan', 'Pending')->count();
        $totalDisetujui = $requests->where('status_pengajuan', 'Approve')->count();
        $totalDitolak = $requests->where('status_pengajuan', 'Reject')->count();

        $periodeAktif = (int) now()->year;
        $totalHakCuti = 12; // Standar hak cuti tahunan per tahun

        $saldoTerpakai = (int) $requests->where('status_pengajuan', 'Approve')
            ->filter(function ($item) use ($periodeAktif) {
                return $item->tanggal_awal && Carbon::parse($item->tanggal_awal)->year == $periodeAktif;
            })
            ->sum(fn($item) => (int) $item->jumlah_hari);

        $sisaPeriodeSebelumnya = ($employee->tanggal_masuk && Carbon::parse($employee->tanggal_masuk)->year >= $periodeAktif) ? 0 : 2;
        $sisaSaldoCuti = max(0, ($totalHakCuti - $saldoTerpakai) + $sisaPeriodeSebelumnya);

        return [
            'totalPengajuan' => $totalPengajuan,
            'totalMenunggu' => $totalMenunggu,
            'totalDisetujui' => $totalDisetujui,
            'totalDitolak' => $totalDitolak,
            'periodeAktif' => $periodeAktif,
            'totalHakCuti' => $totalHakCuti,
            'saldoTerpakai' => $saldoTerpakai,
            'sisaSaldoCuti' => $sisaSaldoCuti,
            'sisaPeriodeSebelumnya' => $sisaPeriodeSebelumnya,
            'tanggalBergabung' => $employee->tanggal_masuk,
        ];
    }
}
