<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EmployeeController;
use App\Models\Announcement;
use App\Models\Fimp;
use App\Models\OvertimeRequest;
use App\Models\PengajuanCuti;
use App\Services\KpiService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends EmployeeController
{
    public function index(): View
    {
        $karyawan = $this->employee();
        $year = now()->year;
        $months = collect(range(1, 12));

        $leave = PengajuanCuti::where('karyawan_id', $karyawan->id);
        $fimp = Fimp::where('karyawan_id', $karyawan->id);
        $overtime = OvertimeRequest::where('karyawan_id', $karyawan->id);

        $activitySeries = $months->map(function (int $month) use ($karyawan, $year) {
            return PengajuanCuti::where('karyawan_id', $karyawan->id)->whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
                + Fimp::where('karyawan_id', $karyawan->id)->whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
                + OvertimeRequest::where('karyawan_id', $karyawan->id)->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        });

        $approvedSeries = $months->map(function (int $month) use ($karyawan, $year) {
            return PengajuanCuti::where('karyawan_id', $karyawan->id)->where('status_pengajuan', 'Approve')->whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
                + Fimp::where('karyawan_id', $karyawan->id)->where('status_approval', 'Approve')->whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
                + OvertimeRequest::where('karyawan_id', $karyawan->id)->where('status', 'Approve')->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        });

        // Cuti metrics
        $cutiApproveCount = (clone $leave)->where('status_pengajuan', 'Approve')->count();
        $cutiApproveDays = (clone $leave)->where('status_pengajuan', 'Approve')->sum('jumlah_hari');
        $terpakaiCuti = $cutiApproveDays > 0 ? $cutiApproveDays : $cutiApproveCount;
        $totalSaldoCuti = 12;
        $sisaCutiTahunan = max(0, $totalSaldoCuti - $terpakaiCuti);

        // Performance indicators — SINGLE SOURCE OF TRUTH dari KpiService.
        // Sama persis dengan halaman KPI: Jan-Agu 2026, Sep-Des null.
        $kpiSummary = KpiService::summary();
        $kpiChart = KpiService::yearlyChart();
        $performanceMonths = $kpiChart['months'];
        $performanceSeries = $kpiChart['series'];

        $latestPerformance = $kpiSummary['latestScore'];
        $averageScore = $kpiSummary['averageScore'];
        $bestScore = $kpiSummary['bestScore'];
        $lowestScore = $kpiSummary['lowestScore'];

        return view('dashboard.index', [
            'karyawan' => $karyawan,
            'tahunDashboard' => $year,
            'cutiMenunggu' => (clone $leave)->where('status_pengajuan', 'Pending')->count(),
            'fimpMenunggu' => (clone $fimp)->where('status_approval', 'Pending')->count(),
            'lemburMenunggu' => (clone $overtime)->where('status', 'Pending')->count(),
            'jumlahPengumuman' => Announcement::active()->count(),
            'cutiDisetujui' => (clone $leave)->where('status_pengajuan', 'Approve')->count(),
            'fimpDisetujui' => (clone $fimp)->where('status_approval', 'Approve')->count(),
            'lemburDisetujui' => (clone $overtime)->where('status', 'Approve')->count(),
            'totalPengajuan' => $leave->count() + $fimp->count() + $overtime->count(),
            'activityMonths' => $months->map(fn (int $month) => Carbon::create($year, $month, 1)->translatedFormat('M'))->all(),
            'activitySeries' => $activitySeries->all(),
            'approvedSeries' => $approvedSeries->all(),
            'totalSaldoCuti' => $totalSaldoCuti,
            'sisaCutiTahunan' => $sisaCutiTahunan,
            'terpakaiCutiTahunan' => $terpakaiCuti,
            'periodeCutiLabel' => 'Periode ' . $year,
            'totalFimp' => $fimp->count(),
            'fimpApproved' => (clone $fimp)->where('status_approval', 'Approve')->count(),
            'fimpPending' => (clone $fimp)->where('status_approval', 'Pending')->count(),
            'totalOvertime' => $overtime->count(),
            'latestPerformance' => $latestPerformance,
            'averageScore' => $averageScore,
            'bestScore' => $bestScore,
            'lowestScore' => $lowestScore,
            'kpiLatestPeriod' => $kpiSummary['latestPeriod'],
            'kpiLatestStatus' => $kpiSummary['latestStatus'],
            'kpiTotalEvaluations' => $kpiSummary['totalEvaluations'],
            'performanceMonths' => $performanceMonths,
            'performanceSeries' => $performanceSeries,
            'pengumuman' => Announcement::active()->latest('published_at')->take(4)->get(),
            'pengajuanCuti' => PengajuanCuti::with('kategoriCuti')->where('karyawan_id', $karyawan->id)->latest()->take(10)->get(),
            'myLeaveRequests' => PengajuanCuti::with('kategoriCuti')->where('karyawan_id', $karyawan->id)->latest()->take(10)->get(),
        ]);
    }
}
