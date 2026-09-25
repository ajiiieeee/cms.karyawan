<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Single source of truth untuk data KPI.
 * Dipakai DashboardController dan PerformanceIndicatorController
 * agar angka, periode, dan format selalu sama persis.
 */
class KpiService
{
    /**
     * Daftar evaluasi kronologis Jan -> Agu 2026 (ascending).
     * Urutan display desc (terbaru dulu) ditangani di collection.
     */
    public static function evaluations(): Collection
    {
        $rows = [
            ['id' => 8, 'periode_kode' => 'Jan 2026', 'periode_label' => 'Januari 2026', 'bulan' => 1, 'kedisiplinan_score' => 23, 'kedisiplinan_max' => 25, 'mengajar_score' => 18, 'mengajar_max' => 30, 'daily_report_score' => 22, 'daily_report_max' => 25, 'teamwork_score' => 19, 'teamwork_max' => 20, 'total_score' => 82, 'total_max' => 100, 'performance_status' => 'Good Performance', 'status_badge' => 'Good', 'catatan' => 'Awal tahun yang baik, penyesuaian jadwal mengajar berjalan lancar.'],
            ['id' => 7, 'periode_kode' => 'Feb 2026', 'periode_label' => 'Februari 2026', 'bulan' => 2, 'kedisiplinan_score' => 24, 'kedisiplinan_max' => 25, 'mengajar_score' => 20, 'mengajar_max' => 30, 'daily_report_score' => 22, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 86, 'total_max' => 100, 'performance_status' => 'Good Performance', 'status_badge' => 'Good', 'catatan' => 'Kerjasama tim sangat baik dalam menyelesaikan evaluasi tengah semester.'],
            ['id' => 6, 'periode_kode' => 'Mar 2026', 'periode_label' => 'Maret 2026', 'bulan' => 3, 'kedisiplinan_score' => 22, 'kedisiplinan_max' => 25, 'mengajar_score' => 13, 'mengajar_max' => 30, 'daily_report_score' => 23, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 78, 'total_max' => 100, 'performance_status' => 'Needs Focus Performance', 'status_badge' => 'Needs Focus', 'catatan' => 'Fokus perbaikan pada materi silabus dan kehadiran kelas pengajaran.'],
            ['id' => 5, 'periode_kode' => 'Apr 2026', 'periode_label' => 'April 2026', 'bulan' => 4, 'kedisiplinan_score' => 23, 'kedisiplinan_max' => 25, 'mengajar_score' => 23, 'mengajar_max' => 30, 'daily_report_score' => 23, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 89, 'total_max' => 100, 'performance_status' => 'Very Good Performance', 'status_badge' => 'Very Good', 'catatan' => 'Capaian pengajaran bulan ini meningkat pesat, teruskan semangatnya.'],
            ['id' => 4, 'periode_kode' => 'Mei 2026', 'periode_label' => 'Mei 2026', 'bulan' => 5, 'kedisiplinan_score' => 24, 'kedisiplinan_max' => 25, 'mengajar_score' => 19, 'mengajar_max' => 30, 'daily_report_score' => 23, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 86, 'total_max' => 100, 'performance_status' => 'Good Performance', 'status_badge' => 'Good', 'catatan' => 'Pelaporan harian selalu tepat waktu, sangat diapresiasi.'],
            ['id' => 3, 'periode_kode' => 'Jun 2026', 'periode_label' => 'Juni 2026', 'bulan' => 6, 'kedisiplinan_score' => 24, 'kedisiplinan_max' => 25, 'mengajar_score' => 18, 'mengajar_max' => 30, 'daily_report_score' => 23, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 85, 'total_max' => 100, 'performance_status' => 'Good Performance', 'status_badge' => 'Good', 'catatan' => 'Pertahankan kedisiplinan dan koordinasi antar tim yang sudah solid.'],
            ['id' => 2, 'periode_kode' => 'Jul 2026', 'periode_label' => 'Juli 2026', 'bulan' => 7, 'kedisiplinan_score' => 24, 'kedisiplinan_max' => 25, 'mengajar_score' => 19, 'mengajar_max' => 30, 'daily_report_score' => 23, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 86, 'total_max' => 100, 'performance_status' => 'Good Performance', 'status_badge' => 'Good', 'catatan' => 'Penyusunan modul ajar sudah bagus, perlu peningkatan interaksi siswa di kelas.'],
            ['id' => 1, 'periode_kode' => 'Agt 2026', 'periode_label' => 'Agustus 2026', 'bulan' => 8, 'kedisiplinan_score' => 24, 'kedisiplinan_max' => 25, 'mengajar_score' => 28, 'mengajar_max' => 30, 'daily_report_score' => 23, 'daily_report_max' => 25, 'teamwork_score' => 20, 'teamwork_max' => 20, 'total_score' => 95, 'total_max' => 100, 'performance_status' => 'Excellent Performance', 'status_badge' => 'Excellent', 'catatan' => 'Angkanya istimewa di pertahankan untuk KPInya'],
        ];

        // Kembalikan desc (terbaru dulu: Agu -> Jan) agar cocok dengan halaman KPI
        return collect($rows)->sortByDesc('bulan')->values();
    }

    public static function summary(): array
    {
        $evaluations = static::evaluations();
        $scores = $evaluations->pluck('total_score');
        $latest = $evaluations->first();

        $best = $scores->max() ?? 0;
        $lowest = $scores->min() ?? 0;

        return [
            'evaluations' => $evaluations,
            'latestScore' => $latest['total_score'] ?? 0,
            'latestStatus' => $latest['status_badge'] ?? '-',
            'latestPeriod' => $latest['periode_label'] ?? '-',
            'averageScore' => $scores->count() ? round($scores->average(), 1) : 0,
            'totalEvaluations' => $evaluations->count(),
            'bestScore' => $best,
            'lowestScore' => $lowest,
            'diffFromBest' => $best - $lowest,
        ];
    }

    /**
     * Series 12 bulan Jan-Des untuk chart Dashboard.
     * Bulan tanpa data (Sep-Des) = null agar garis terputus jujur,
     * bukan angka karangan.
     */
    public static function yearlyChart(): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $byMonth = static::evaluations()->keyBy('bulan');
        $series = [];
        foreach (range(1, 12) as $m) {
            $series[] = isset($byMonth[$m]) ? (float) $byMonth[$m]['total_score'] : null;
        }

        return ['months' => $months, 'series' => $series];
    }

    /** Format tunggal: 1 desimal + satuan /100. Contoh: 95.0/100 */
    public static function formatScore(float|int|null $value): string
    {
        return number_format((float) ($value ?? 0), 1);
    }
}
