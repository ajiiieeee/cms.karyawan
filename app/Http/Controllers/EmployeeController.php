<?php
namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Support\Facades\Auth;

abstract class EmployeeController extends Controller
{
    protected function employee(): Karyawan
    {
        $user = Auth::user();
        abort_unless($user, 401);
        return Karyawan::where('username', $user->username)
            ->orWhere('email', $user->email)
            ->firstOrFail();
    }
}
