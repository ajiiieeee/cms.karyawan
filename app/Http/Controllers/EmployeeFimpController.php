<?php
namespace App\Http\Controllers;

use App\Models\Fimp;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeFimpController extends EmployeeController
{
    public function index(): View { return view('employee.fimp.index',['requests'=>Fimp::with('penggantiKaryawan')->where('karyawan_id',$this->employee()->id)->latest()->get()]); }
    public function create(): View { return view('employee.fimp.form',['requestItem'=>new Fimp(),'substitutes'=>Karyawan::where('status_akun','aktif')->whereKeyNot($this->employee()->id)->orderBy('nama_karyawan')->get()]); }
    public function store(Request $request): RedirectResponse { $employee=$this->employee(); $data=$request->validate(['tanggal_awal'=>'required|date','tanggal_akhir'=>'required|date|after_or_equal:tanggal_awal','karyawan_pengganti'=>'required|exists:karyawans,id|different:karyawan_id','keperluan'=>'required|string|max:2000']); $days=Carbon::parse($data['tanggal_awal'])->diffInDays(Carbon::parse($data['tanggal_akhir']))+1; Fimp::create($data+['karyawan_id'=>$employee->id,'total_hari'=>$days,'pengganti_mengetahui'=>true,'pengganti_bersedia'=>true,'status_pengajuan'=>0,'status_approval'=>'Pending','created_date'=>now()->toDateString(),'created_by'=>auth()->user()->nama]); return redirect()->route('fimp.index')->with('success','Pengajuan FIMP berhasil dikirim.'); }
    public function edit(Fimp $fimp): View { $this->owned($fimp); abort_unless($fimp->status_approval==='Pending',403); return view('employee.fimp.form',['requestItem'=>$fimp,'substitutes'=>Karyawan::where('status_akun','aktif')->whereKeyNot($this->employee()->id)->orderBy('nama_karyawan')->get()]); }
    public function update(Request $request,Fimp $fimp): RedirectResponse { $this->owned($fimp); abort_unless($fimp->status_approval==='Pending',403); $data=$request->validate(['tanggal_awal'=>'required|date','tanggal_akhir'=>'required|date|after_or_equal:tanggal_awal','karyawan_pengganti'=>'required|exists:karyawans,id','keperluan'=>'required|string|max:2000']); $data['total_hari']=Carbon::parse($data['tanggal_awal'])->diffInDays(Carbon::parse($data['tanggal_akhir']))+1; $fimp->update($data+['updated_date'=>now()->toDateString(),'updated_by'=>auth()->user()->nama]); return redirect()->route('fimp.index')->with('success','Pengajuan FIMP diperbarui.'); }
    public function destroy(Fimp $fimp): RedirectResponse { $this->owned($fimp); abort_unless($fimp->status_approval==='Pending',403); $fimp->delete(); return back()->with('success','Pengajuan FIMP dihapus.'); }
    private function owned(Fimp $item): void { abort_unless($item->karyawan_id===$this->employee()->id,404); }
}
