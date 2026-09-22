<?php
namespace App\Http\Controllers;

use App\Models\KategoriCuti;
use App\Models\PengajuanCuti;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeLeaveController extends EmployeeController
{
    public function index(): View { return view('employee.leave.index', ['requests' => PengajuanCuti::with('kategoriCuti')->where('karyawan_id',$this->employee()->id)->latest()->get()]); }
    public function create(): View { return view('employee.leave.form', ['requestItem' => new PengajuanCuti(), 'categories' => KategoriCuti::orderBy('nama_kategori')->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate(['tanggal_awal'=>'required|date','tanggal_akhir'=>'required|date|after_or_equal:tanggal_awal','jenis_cuti'=>'required|exists:kategori_cuti,id','keterangan'=>'nullable|string|max:1000']);
        $days=Carbon::parse($data['tanggal_awal'])->diffInDays(Carbon::parse($data['tanggal_akhir']))+1;
        PengajuanCuti::create($data+['karyawan_id'=>$this->employee()->id,'jumlah_hari'=>(string)$days,'status_approval'=>0,'status_pengajuan'=>'Pending','created_date'=>now()->toDateString(),'created_by'=>auth()->user()->nama]);
        return redirect()->route('leave-requests.index')->with('success','Permohonan cuti berhasil dikirim.');
    }
    public function edit(PengajuanCuti $leave_request): View { $this->owned($leave_request); abort_unless($leave_request->status_pengajuan==='Pending',403); return view('employee.leave.form',['requestItem'=>$leave_request,'categories'=>KategoriCuti::orderBy('nama_kategori')->get()]); }
    public function update(Request $request, PengajuanCuti $leave_request): RedirectResponse { $this->owned($leave_request); abort_unless($leave_request->status_pengajuan==='Pending',403); $data=$request->validate(['tanggal_awal'=>'required|date','tanggal_akhir'=>'required|date|after_or_equal:tanggal_awal','jenis_cuti'=>'required|exists:kategori_cuti,id','keterangan'=>'nullable|string|max:1000']); $data['jumlah_hari']=(string)(Carbon::parse($data['tanggal_awal'])->diffInDays(Carbon::parse($data['tanggal_akhir']))+1); $leave_request->update($data+['updated_date'=>now()->toDateString(),'updated_by'=>auth()->user()->nama]); return redirect()->route('leave-requests.index')->with('success','Permohonan cuti diperbarui.'); }
    public function destroy(PengajuanCuti $leave_request): RedirectResponse { $this->owned($leave_request); abort_unless($leave_request->status_pengajuan==='Pending',403); $leave_request->delete(); return back()->with('success','Permohonan cuti dihapus.'); }
    private function owned(PengajuanCuti $item): void { abort_unless($item->karyawan_id===$this->employee()->id,404); }
}
