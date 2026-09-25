<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EmployeeController;
use App\Services\KpiService;
use Illuminate\View\View;

class PerformanceIndicatorController extends EmployeeController
{
    /**
     * Get dummy KPI evaluations list matching reference data.
     * Delegasi ke KpiService agar sama persis dengan Dashboard.
     */
    protected function getDummyEvaluations(): array
    {
        return KpiService::evaluations()->all();
    }

    /**
     * Display a listing of KPI evaluations (Image 1).
     */
    public function index(): View
    {
        $karyawan = $this->employee();
        $summary = KpiService::summary();

        return view('performance-indicator.index', [
            'karyawan' => $karyawan,
            'evaluations' => $summary['evaluations'],
            'latestScore' => $summary['latestScore'],
            'latestStatus' => $summary['latestStatus'],
            'latestPeriod' => $summary['latestPeriod'],
            'averageScore' => $summary['averageScore'],
            'totalEvaluations' => $summary['totalEvaluations'],
            'bestScore' => $summary['bestScore'],
            'lowestScore' => $summary['lowestScore'],
            'diffFromBest' => $summary['diffFromBest'],
        ]);
    }

    /**
     * Display detail of a specific KPI evaluation (Image 2).
     */
    public function show(int $id): View
    {
        $karyawan = $this->employee();
        $evaluations = KpiService::evaluations();

        $item = $evaluations->firstWhere('id', $id) ?? $evaluations->first();

        // Calculate aspect percentages
        $kedisiplinanPct = round(($item['kedisiplinan_score'] / $item['kedisiplinan_max']) * 100, 1);
        $mengajarPct = round(($item['mengajar_score'] / $item['mengajar_max']) * 100, 1);
        $dailyReportPct = round(($item['daily_report_score'] / $item['daily_report_max']) * 100, 1);
        $teamworkPct = round(($item['teamwork_score'] / $item['teamwork_max']) * 100, 1);

        return view('performance-indicator.show', [
            'karyawan' => $karyawan,
            'item' => $item,
            'kedisiplinanPct' => $kedisiplinanPct,
            'mengajarPct' => $mengajarPct,
            'dailyReportPct' => $dailyReportPct,
            'teamworkPct' => $teamworkPct,
        ]);
    }
}
