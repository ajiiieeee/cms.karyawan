<?php
namespace App\Http\Controllers\Payroll;
use App\Http\Controllers\EmployeeController;
use Illuminate\View\View;
class PayrollController extends EmployeeController { public function index(): View { return view('payroll.index',['karyawan'=>$this->employee(),'payrolls'=>collect()]); } }
