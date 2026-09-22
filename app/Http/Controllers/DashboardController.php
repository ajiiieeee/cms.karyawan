<?php

namespace App\Http\Controllers;

use App\Models\BidangStudi;
use App\Models\DetailPendaftaran;
use App\Models\Fimp;
use App\Models\Karyawan;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\PengajuanCuti;
use App\Models\Penjadwalan;
use App\Models\RiwayatPembayaran;
use App\Models\SaldoCuti;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Nama-nama bulan dalam bahasa Indonesia
     */
    protected array $monthNamesIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    /**
     * Singkatan bulan dalam bahasa Indonesia
     */
    protected array $monthShortNamesIndo = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    /**
     * Pemetaan nama hari Indonesia ke index dayOfWeek Carbon (0=Minggu, 1=Senin, dst.)
     */
    protected array $dayMapIndo = [
        'minggu' => 0,
        'senin'  => 1,
        'selasa' => 2,
        'rabu'   => 3,
        'kamis'  => 4,
        'jumat'  => 5,
        'sabtu'  => 6,
    ];

    /**
     * Nama hari dalam bahasa Indonesia berdasarkan dayOfWeek Carbon (0=Minggu, 1=Senin, dst.)
     */
    protected array $dayNamesIndo = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    public function index(Request $request): View
    {
        $user = auth()->user();
        if ($user) {
            $user->load('grup');
        }

        // Tampilkan Dashboard Karyawan jika user adalah Staff/Karyawan atau parameter karyawan disertakan
        $isStaff = $user && $user->grup && in_array(strtolower($user->grup->nama_grup), ['staff', 'karyawan']);
        if ($isStaff || $request->has('karyawan') || $request->get('view') === 'karyawan') {
            return $this->karyawanDashboard($request);
        }

        $availableYears = $this->getAvailableYears();
        $periodInfo = $this->resolvePeriod($request, $availableYears);

        // Ambil data statistik 9 cards
        $stats = $this->getDashboardStats($periodInfo['start'], $periodInfo['end']);

        // Ambil data 4 chart
        $chartData = $this->getChartData($periodInfo['start'], $periodInfo['end'], $periodInfo['grouping']);

        // Ambil data event jadwal kalender
        $calendarEvents = $this->getCalendarScheduleEvents($periodInfo['start'], $periodInfo['end']);

        // Ambil top 5 bidang studi
        $topBidangData = $this->getTopBidangStudi($periodInfo['start'], $periodInfo['end']);
        $topBidangStudi = $topBidangData['items'];
        $maxTopPeminat = $topBidangData['max_total'];

        // Recent Leave Requests
        $leaveRequests = PengajuanCuti::with(['karyawan', 'kategoriCuti'])
            ->latest()
            ->take(5)
            ->get();

        // Notice Board Items: Log aktivitas terbaru dari Pendaftaran & Pembayaran
        $noticeBoardItems = $this->getNoticeBoardItems(10);
        $notifications = $noticeBoardItems;

        // Variabel untuk view
        $chartYear = $periodInfo['year'];
        $selectedPeriod = $periodInfo['period'];
        $selectedMonth = $periodInfo['month'];
        $selectedWeek = $periodInfo['week'];
        $startDateStr = $periodInfo['start']->format('d/m/Y');
        $endDateStr = $periodInfo['end']->format('d/m/Y');
        $periodLabel = $periodInfo['label'];

        // Card statistics variables
        $totalSiswa = $stats['total_siswa'];
        $totalSiswaNonaktif = $stats['total_siswa_nonaktif'];
        $totalSiswaAktif = $stats['total_siswa_aktif'];
        $totalJadwal = $stats['total_jadwal'];
        $totalPendaftaran = $stats['total_pendaftaran'];
        $totalTransaksi = $stats['total_transaksi'];
        $totalPembayaran = $stats['total_pembayaran'];
        $totalPiutang = $stats['total_piutang'];
        $totalTarget = $stats['total_target'];
        $persenRealisasi = $stats['persentase_realisasi'];

        // Chart variables
        $trendPendaftaranLabels = $chartData['pendaftaran']['labels'];
        $trendPendaftaranData   = $chartData['pendaftaran']['data'];
        $pendapatanLabels       = $chartData['pendapatan']['labels'];
        $pendapatanMasukData    = $chartData['pendapatan']['masuk'];
        $pendapatanBelumMasukData = $chartData['pendapatan']['belum_masuk'];
        $pendapatanTargetData   = $chartData['pendapatan']['target'];
        $piutangLabels          = $chartData['piutang']['labels'];
        $piutangData            = $chartData['piutang']['data'];
        $piutangSiswaCountData  = $chartData['piutang']['siswa_count'];
        $totalSiswaPiutangPeriode = $chartData['piutang']['total_siswa_piutang'];
        $distribusiLabels       = $chartData['bidang_studi']['labels'];
        $distribusiData         = $chartData['bidang_studi']['data'];

        $totalMasukPeriode      = $chartData['pendapatan']['total_masuk'];
        $totalBelumMasukPeriode = $chartData['pendapatan']['total_belum_masuk'];
        $totalTargetPeriode     = $chartData['pendapatan']['total_target'];
        $persentaseRealisasiPeriode = $chartData['pendapatan']['persentase_realisasi'];

        // Kompatibilitas mundur
        $dataPendaftaran = $trendPendaftaranData;
        $dataPendapatan = $pendapatanMasukData;
        $dataPiutang = $piutangData;
        $dataBidangStudi = $distribusiData;
        $totalPembayaranBulan = $totalPembayaran;
        $totalPembayaranBulanIni = $totalPembayaran;
        $totalPiutangBulan = $totalPiutang;
        $totalTargetBulan = $totalTarget;
        $totalPembayaranTahun = $totalPembayaran;
        $totalPiutangTahun = $totalPiutang;
        $totalTargetTahun = $totalTarget;
        $totalTargetTahunan = $totalTarget;
        $totalRealisasiTahunan = $totalPembayaran;
        $totalSiswaBerjalan = $totalSiswaAktif;
        $totalSiswaTidakAktif = $totalSiswaNonaktif;
        $pendaftaranBulanIni = $totalPendaftaran;
        $siswaBulanIni = $totalSiswa;
        $totalBidangStudi = BidangStudi::count();
        $totalKaryawan = Karyawan::count();
        $totalKelas = $totalJadwal;

        return view('dashboard.index', compact(
            'user',
            'chartYear',
            'availableYears',
            'selectedPeriod',
            'selectedMonth',
            'selectedWeek',
            'startDateStr',
            'endDateStr',
            'periodLabel',
            'totalSiswa',
            'totalSiswaNonaktif',
            'totalSiswaAktif',
            'totalJadwal',
            'totalPendaftaran',
            'totalTransaksi',
            'totalPembayaran',
            'totalPiutang',
            'totalTarget',
            'persenRealisasi',
            'trendPendaftaranLabels',
            'trendPendaftaranData',
            'dataPendaftaran',
            'pendapatanLabels',
            'pendapatanMasukData',
            'pendapatanBelumMasukData',
            'pendapatanTargetData',
            'dataPendapatan',
            'totalMasukPeriode',
            'totalBelumMasukPeriode',
            'totalTargetPeriode',
            'persentaseRealisasiPeriode',
            'piutangLabels',
            'piutangData',
            'piutangSiswaCountData',
            'totalSiswaPiutangPeriode',
            'dataPiutang',
            'distribusiLabels',
            'distribusiData',
            'dataBidangStudi',
            'topBidangStudi',
            'maxTopPeminat',
            'calendarEvents',
            'leaveRequests',
            'noticeBoardItems',
            'notifications',
            'totalPembayaranBulan',
            'totalPembayaranBulanIni',
            'totalPiutangBulan',
            'totalTargetBulan',
            'totalPembayaranTahun',
            'totalPiutangTahun',
            'totalTargetTahun',
            'totalTargetTahunan',
            'totalRealisasiTahunan',
            'totalSiswaBerjalan',
            'totalSiswaTidakAktif',
            'pendaftaranBulanIni',
            'siswaBulanIni',
            'totalBidangStudi',
            'totalKaryawan',
            'totalKelas'
        ));
    }

    /**
     * Tampilan Dashboard Karyawan
     */
    public function karyawanDashboard(Request $request): View
    {
        $user = auth()->user();
        if ($user) {
            $user->load('grup');
        }

        // Cari data karyawan yang terkait dengan user login
        $karyawan = null;
        if ($user) {
            $karyawan = Karyawan::where('username', $user->username)
                ->orWhere('email', $user->email)
                ->first();

            if (!$karyawan && !empty($user->nama)) {
                $karyawan = Karyawan::where('nama_karyawan', 'like', '%' . $user->nama . '%')->first();
            }
        }

        // Fallback preview khusus jika admin/dev melakukan review tampilan
        if (!$karyawan && ($request->has('karyawan_id') || $request->has('karyawan') || $request->get('view') === 'karyawan')) {
            $targetId = $request->get('karyawan_id');
            $karyawan = $targetId ? Karyawan::find($targetId) : Karyawan::where('status_akun', 'aktif')->first();
        }

        // 1. Data Saldo Cuti
        $saldoCuti = $karyawan ? $karyawan->saldoCuti : null;
        $totalSaldoCuti = $saldoCuti ? $saldoCuti->total_cuti : null;
        $sisaCutiTahunan = $saldoCuti ? $saldoCuti->saldo_sisa : null;
        $terpakaiCutiTahunan = $saldoCuti ? $saldoCuti->saldo_terpakai : 0;
        $periodeCutiLabel = $saldoCuti && $saldoCuti->periode_mulai && $saldoCuti->periode_selesai
            ? $saldoCuti->periode_mulai->format('d/m/Y') . ' - ' . $saldoCuti->periode_selesai->format('d/m/Y')
            : null;

        // 2. Data Izin Meninggalkan Pekerjaan (FIMP)
        $totalFimp = $karyawan ? Fimp::where('karyawan_id', $karyawan->id)->count() : 0;
        $fimpApproved = $karyawan ? Fimp::where('karyawan_id', $karyawan->id)->where('status_approval', 'Approve')->count() : 0;
        $fimpPending = $karyawan ? Fimp::where('karyawan_id', $karyawan->id)->where('status_approval', 'Pending')->count() : 0;

        // 3. Overtime Request (tidak ada tabel lembur di DB, state aman)
        $totalOvertime = null;

        // 4. Data Performance (tidak ada tabel performa di DB, state aman null)
        $latestPerformance = null;
        $averageScore = null;
        $bestScore = null;
        $lowestScore = null;
        $performancePeriod = 'Tahun ' . date('Y');
        $performanceMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $performanceSeries = [];

        // 5. List Pengajuan Cuti Khusus Karyawan yang Sedang Login
        $myLeaveRequests = $karyawan
            ? PengajuanCuti::with('kategoriCuti')
                ->where('karyawan_id', $karyawan->id)
                ->latest('id')
                ->take(10)
                ->get()
            : collect();

        return view('dashboard.karyawan.index', compact(
            'user',
            'karyawan',
            'saldoCuti',
            'totalSaldoCuti',
            'sisaCutiTahunan',
            'terpakaiCutiTahunan',
            'periodeCutiLabel',
            'totalFimp',
            'fimpApproved',
            'fimpPending',
            'totalOvertime',
            'latestPerformance',
            'averageScore',
            'bestScore',
            'lowestScore',
            'performancePeriod',
            'performanceMonths',
            'performanceSeries',
            'myLeaveRequests'
        ));
    }

    /**
     * Endpoint JSON AJAX terpadu untuk Dashboard:
     * Mengembalikan 9 Card Statistik, 4 Chart, Kalender Jadwal, dan Top Bidang Studi.
     */
    public function dashboardData(Request $request): JsonResponse
    {
        $availableYears = $this->getAvailableYears();
        $periodInfo = $this->resolvePeriod($request, $availableYears);

        $stats = $this->getDashboardStats($periodInfo['start'], $periodInfo['end']);
        $chartData = $this->getChartData($periodInfo['start'], $periodInfo['end'], $periodInfo['grouping']);
        $calendarEvents = $this->getCalendarScheduleEvents($periodInfo['start'], $periodInfo['end']);
        $topBidangData = $this->getTopBidangStudi($periodInfo['start'], $periodInfo['end']);

        return response()->json([
            'success'          => true,
            'period'           => [
                'type'       => $periodInfo['period'],
                'year'       => $periodInfo['year'],
                'month'      => $periodInfo['month'],
                'week'       => $periodInfo['week'],
                'start_date' => $periodInfo['start']->toDateString(),
                'end_date'   => $periodInfo['end']->toDateString(),
                'label'      => $periodInfo['label'],
            ],
            'cards'            => $stats,
            'charts'           => $chartData,
            'calendar_events'  => $calendarEvents,
            'top_bidang_studi' => $topBidangData['items'],
            'max_top_peminat'  => $topBidangData['max_total'],
            'available_years'  => $availableYears,
            'notice_board'     => $this->getNoticeBoardItems(10),
        ]);
    }

    /**
     * Endpoint kompatibilitas mundur chartData
     */
    public function chartData(Request $request): JsonResponse
    {
        return $this->dashboardData($request);
    }

    /**
     * Endpoint detail sesi jadwal berdasarkan tanggal (untuk kalender tanpa modal)
     */
    public function scheduleDetail(Request $request): JsonResponse
    {
        $rawDate = $request->input('date', Carbon::today()->toDateString());
        try {
            $targetDate = str_contains($rawDate, '/')
                ? Carbon::createFromFormat('d/m/Y', $rawDate)
                : Carbon::parse($rawDate);
        } catch (\Exception $e) {
            $targetDate = Carbon::today();
        }

        $dateKey = $targetDate->format('Y-m-d');
        $events = $this->getCalendarScheduleEvents($targetDate->copy()->startOfDay(), $targetDate->copy()->endOfDay());
        $dayEvents = $events[$dateKey] ?? [];

        $monthName = $this->monthNamesIndo[$targetDate->month] ?? $targetDate->format('F');
        $dayName = $this->dayNamesIndo[$targetDate->dayOfWeek] ?? $targetDate->format('l');
        $formattedDate = "{$dayName}, {$targetDate->day} {$monthName} {$targetDate->year}";

        return response()->json([
            'success'        => true,
            'date'           => $dateKey,
            'formatted_date' => $formattedDate,
            'total'          => count($dayEvents),
            'events'         => $dayEvents,
        ]);
    }

    /**
     * Resolusi periode filter (tahunan, bulanan, mingguan, rentang tanggal)
     */
    protected function resolvePeriod(Request $request, array $availableYears): array
    {
        $now = Carbon::now();
        $defaultYear = in_array($now->year, $availableYears) ? $now->year : ($availableYears[0] ?? $now->year);

        // Menampung mode dari input 'period' atau 'mode'
        $rawPeriod = strtolower((string) $request->input('period', $request->input('mode', 'monthly')));

        $period = match ($rawPeriod) {
            'yearly', 'tahun', 'tahunan' => 'yearly',
            'weekly', 'minggu', 'mingguan' => 'weekly',
            'custom', 'rentang', 'range' => 'custom',
            default => 'monthly',
        };

        $year = (int) $request->input('year', $defaultYear);
        if (!in_array($year, $availableYears) && !empty($availableYears)) {
            $year = $availableYears[0];
        }

        $month = (int) $request->input('month', $now->month);
        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        $week = (int) $request->input('week', $now->weekOfYear);
        if ($week < 1 || $week > 53) {
            $week = $now->weekOfYear;
        }

        if ($period === 'yearly') {
            $start = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $end = Carbon::createFromDate($year, 12, 31)->endOfYear();
            $label = "Tahun {$year}";
            $grouping = 'monthly';
        } elseif ($period === 'weekly') {
            $baseWeek = Carbon::now()->setISODate($year, $week);
            $start = $baseWeek->copy()->startOfWeek();
            $end = $baseWeek->copy()->endOfWeek();
            $label = "Minggu ke-{$week} ({$start->format('d M')} - {$end->format('d M Y')})";
            $grouping = 'daily';
        } elseif ($period === 'custom') {
            $startInput = $request->input('start_date');
            $endInput = $request->input('end_date');

            if ($startInput && $endInput) {
                try {
                    $start = str_contains($startInput, '/')
                        ? Carbon::createFromFormat('d/m/Y', $startInput)->startOfDay()
                        : Carbon::parse($startInput)->startOfDay();
                } catch (\Exception $e) {
                    $start = $now->copy()->startOfMonth();
                }

                try {
                    $end = str_contains($endInput, '/')
                        ? Carbon::createFromFormat('d/m/Y', $endInput)->endOfDay()
                        : Carbon::parse($endInput)->endOfDay();
                } catch (\Exception $e) {
                    $end = $now->copy()->endOfMonth();
                }

                if ($start->gt($end)) {
                    $temp = $start;
                    $start = $end->copy()->startOfDay();
                    $end = $temp->copy()->endOfDay();
                }
            } else {
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
            }

            $diffDays = $start->diffInDays($end);
            if ($diffDays <= 90) {
                $grouping = 'daily';
            } elseif ($diffDays <= 365) {
                $grouping = 'weekly';
            } else {
                $grouping = 'monthly';
            }
            $label = "{$start->format('d M Y')} - {$end->format('d M Y')}";
        } else {
            // monthly (default)
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $monthName = $this->monthNamesIndo[$month] ?? $start->format('F');
            $label = "{$monthName} {$year}";
            $grouping = 'daily';
        }

        return [
            'period'   => $period,
            'year'     => $year,
            'month'    => $month,
            'week'     => $week,
            'start'    => $start,
            'end'      => $end,
            'label'    => $label,
            'grouping' => $grouping,
        ];
    }

    /**
     * Hitung 9 Card Statistik
     */
    protected function getDashboardStats(Carbon $start, Carbon $end): array
    {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        // 1. Total Siswa (aktif + nonaktif pada periode)
        $totalSiswa = Siswa::whereBetween('created_at', [$start, $end])->count();
        $totalSiswaAktif = Siswa::whereBetween('created_at', [$start, $end])
            ->where('status_siswa', 1)
            ->count();
        $totalSiswaNonaktif = Siswa::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->where('status_siswa', 0)->orWhereNull('status_siswa');
            })
            ->count();

        // Total akumulasi seluruh siswa untuk konteks
        $totalSiswaAll = Siswa::count();
        $totalSiswaAktifAll = Siswa::where('status_siswa', 1)->count();
        $totalSiswaNonaktifAll = Siswa::where(function ($q) {
            $q->where('status_siswa', 0)->orWhereNull('status_siswa');
        })->count();

        // 4. Total Jadwal: Penjadwalan aktif / terjadwal pada periode ini
        $totalJadwal = Penjadwalan::where(function ($q) use ($startStr, $endStr) {
            $q->where('tgl_mulai', '<=', $endStr)
            ->where(function ($sq) use ($startStr) {
                $sq->where('tgl_selesai', '>=', $startStr)
                    ->orWhereNull('tgl_selesai');
            });
        })
        ->where('status_jadwal', 0)
        ->count();

        // 5. Total Pendaftaran pada periode
        $totalPendaftaran = Pendaftaran::whereBetween('tanggal_pendaftaran', [$startStr, $endStr])->count();

        // 6. Total Transaksi (Riwayat Pembayaran)
        $totalTransaksi = RiwayatPembayaran::whereBetween('tanggal_bayar', [$startStr, $endStr])->count();

        // 7, 8, 9. Keuangan Pembayaran, Piutang, Target
        // Target = Pembayaran (uang masuk riil) + Piutang (sisa tagihan)
        $pembayaranQuery = Pembayaran::whereBetween('tanggal_pembayaran', [$startStr, $endStr]);
        $totalPembayaran = (int) (clone $pembayaranQuery)->sum(DB::raw('jumlah_tagihan - sisa_tagihan'));
        $totalPiutang = (int) (clone $pembayaranQuery)->sum('sisa_tagihan');
        $totalTarget = (int) (clone $pembayaranQuery)->sum('jumlah_tagihan');

        // Pastikan rumus finansial selalu konsisten: Target = Pembayaran + Piutang
        if ($totalTarget !== ($totalPembayaran + $totalPiutang)) {
            $totalTarget = $totalPembayaran + $totalPiutang;
        }

        $persentaseRealisasi = $totalTarget > 0 ? round(($totalPembayaran / $totalTarget) * 100, 1) : 0;

        return [
            'total_siswa'          => $totalSiswa,
            'total_siswa_nonaktif' => $totalSiswaNonaktif,
            'total_siswa_aktif'    => $totalSiswaAktif,
            'total_siswa_all'      => $totalSiswaAll,
            'total_siswa_aktif_all' => $totalSiswaAktifAll,
            'total_siswa_nonaktif_all' => $totalSiswaNonaktifAll,
            'total_jadwal'         => $totalJadwal,
            'total_pendaftaran'    => $totalPendaftaran,
            'total_transaksi'      => $totalTransaksi,
            'total_pembayaran'     => $totalPembayaran,
            'total_piutang'        => $totalPiutang,
            'total_target'         => $totalTarget,
            'persentase_realisasi' => $persentaseRealisasi,
            'formatted_pembayaran' => 'Rp ' . number_format($totalPembayaran, 0, ',', '.'),
            'formatted_piutang'    => 'Rp ' . number_format($totalPiutang, 0, ',', '.'),
            'formatted_target'     => 'Rp ' . number_format($totalTarget, 0, ',', '.'),
        ];
    }

    /**
     * Hitung Data 4 Chart Utama
     */
    protected function getChartData(Carbon $start, Carbon $end, string $grouping): array
    {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $labels = [];
        $pendaftarans = [];
        $pembayarans = collect();

        if ($grouping === 'monthly') {
            // Grouping per bulan (1..12 jika setahun, atau sesuai bulan dalam rentang)
            if ($start->year === $end->year && $start->month === 1 && $end->month === 12) {
                for ($m = 1; $m <= 12; $m++) {
                    $labels[$m] = $this->monthShortNamesIndo[$m] ?? Carbon::createFromDate($start->year, $m, 1)->format('M');
                }

                $pendaftarans = DB::table('pendaftaran')
                    ->whereBetween('tanggal_pendaftaran', [$startStr, $endStr])
                    ->selectRaw('MONTH(tanggal_pendaftaran) as k, COUNT(*) as total')
                    ->groupBy('k')
                    ->pluck('total', 'k')
                    ->toArray();

                $pembayarans = DB::table('pembayaran')
                    ->whereBetween('tanggal_pembayaran', [$startStr, $endStr])
                    ->selectRaw('
                        MONTH(tanggal_pembayaran) as k,
                        SUM(jumlah_tagihan - sisa_tagihan) as masuk,
                        SUM(sisa_tagihan) as belum_masuk,
                        SUM(jumlah_tagihan) as target,
                        COUNT(DISTINCT CASE WHEN sisa_tagihan > 0 THEN siswa_id ELSE NULL END) as siswa_piutang
                    ')
                    ->groupBy('k')
                    ->get()
                    ->keyBy('k');
            } else {
                $curr = $start->copy()->startOfMonth();
                while ($curr->lte($end)) {
                    $k = $curr->format('Y-m');
                    $mIndo = $this->monthShortNamesIndo[$curr->month] ?? $curr->format('M');
                    $labels[$k] = $mIndo . ' ' . $curr->format('y');
                    $curr->addMonth();
                }

                $pendaftarans = DB::table('pendaftaran')
                    ->whereBetween('tanggal_pendaftaran', [$startStr, $endStr])
                    ->selectRaw("DATE_FORMAT(tanggal_pendaftaran, '%Y-%m') as k, COUNT(*) as total")
                    ->groupBy('k')
                    ->pluck('total', 'k')
                    ->toArray();

                $pembayarans = DB::table('pembayaran')
                    ->whereBetween('tanggal_pembayaran', [$startStr, $endStr])
                    ->selectRaw("
                        DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as k,
                        SUM(jumlah_tagihan - sisa_tagihan) as masuk,
                        SUM(sisa_tagihan) as belum_masuk,
                        SUM(jumlah_tagihan) as target,
                        COUNT(DISTINCT CASE WHEN sisa_tagihan > 0 THEN siswa_id ELSE NULL END) as siswa_piutang
                    ")
                    ->groupBy('k')
                    ->get()
                    ->keyBy('k');
            }
        } elseif ($grouping === 'weekly') {
            // Grouping per minggu dalam rentang
            $curr = $start->copy()->startOfWeek();
            while ($curr->lte($end)) {
                $weekEnd = $curr->copy()->endOfWeek();
                $k = $curr->format('Y-W');
                $labels[$k] = $curr->format('d M') . ' - ' . $weekEnd->format('d M');
                $curr->addWeek();
            }

            $pendaftarans = DB::table('pendaftaran')
                ->whereBetween('tanggal_pendaftaran', [$startStr, $endStr])
                ->selectRaw("DATE_FORMAT(tanggal_pendaftaran, '%Y-%v') as k, COUNT(*) as total")
                ->groupBy('k')
                ->pluck('total', 'k')
                ->toArray();

            $pembayarans = DB::table('pembayaran')
                ->whereBetween('tanggal_pembayaran', [$startStr, $endStr])
                ->selectRaw("
                    DATE_FORMAT(tanggal_pembayaran, '%Y-%v') as k,
                    SUM(jumlah_tagihan - sisa_tagihan) as masuk,
                    SUM(sisa_tagihan) as belum_masuk,
                    SUM(jumlah_tagihan) as target,
                    COUNT(DISTINCT CASE WHEN sisa_tagihan > 0 THEN siswa_id ELSE NULL END) as siswa_piutang
                ")
                ->groupBy('k')
                ->get()
                ->keyBy('k');
        } else {
            // Grouping Harian (Daily)
            $isWeeklyRange = ($start->diffInDays($end) <= 7 && $start->isMonday());
            $curr = $start->copy();
            while ($curr->lte($end)) {
                $k = $curr->format('Y-m-d');
                if ($isWeeklyRange) {
                    $dayName = $this->dayNamesIndo[$curr->dayOfWeek] ?? $curr->format('l');
                    $labels[$k] = "{$dayName} ({$curr->format('d/m')})";
                } else {
                    $labels[$k] = $curr->format('d M');
                }
                $curr->addDay();
            }

            $pendaftarans = DB::table('pendaftaran')
                ->whereBetween('tanggal_pendaftaran', [$startStr, $endStr])
                ->selectRaw('DATE(tanggal_pendaftaran) as k, COUNT(*) as total')
                ->groupBy('k')
                ->pluck('total', 'k')
                ->toArray();

            $pembayarans = DB::table('pembayaran')
                ->whereBetween('tanggal_pembayaran', [$startStr, $endStr])
                ->selectRaw('
                    DATE(tanggal_pembayaran) as k,
                    SUM(jumlah_tagihan - sisa_tagihan) as masuk,
                    SUM(sisa_tagihan) as belum_masuk,
                    SUM(jumlah_tagihan) as target,
                    COUNT(DISTINCT CASE WHEN sisa_tagihan > 0 THEN siswa_id ELSE NULL END) as siswa_piutang
                ')
                ->groupBy('k')
                ->get()
                ->keyBy('k');
        }

        $resLabels = [];
        $pendaftaranData = [];
        $pendapatanMasuk = [];
        $pendapatanBelumMasuk = [];
        $pendapatanTarget = [];
        $piutangData = [];
        $piutangSiswaCount = [];

        foreach ($labels as $k => $lbl) {
            $resLabels[] = $lbl;
            $pendaftaranData[] = (int) ($pendaftarans[$k] ?? 0);

            $rec = $pembayarans->get($k);
            $mVal = (int) ($rec->masuk ?? 0);
            $bmVal = (int) ($rec->belum_masuk ?? 0);
            $tVal = (int) ($rec->target ?? ($mVal + $bmVal));
            $scVal = (int) ($rec->siswa_piutang ?? 0);

            $pendapatanMasuk[] = $mVal;
            $pendapatanBelumMasuk[] = $bmVal;
            $pendapatanTarget[] = $tVal;
            $piutangData[] = $bmVal;
            $piutangSiswaCount[] = $scVal;
        }

        // 4. Distribusi Bidang Studi pada periode aktif
        $bidangCounts = DB::table('detail_pendaftaran')
            ->join('pendaftaran', 'detail_pendaftaran.pendaftaran_id', '=', 'pendaftaran.id')
            ->join('bidang_studis', 'detail_pendaftaran.bidang_studi_id', '=', 'bidang_studis.id')
            ->whereBetween('pendaftaran.tanggal_pendaftaran', [$startStr, $endStr])
            ->select('bidang_studis.nama_bidang_studi as nama', DB::raw('COUNT(detail_pendaftaran.id) as total'))
            ->groupBy('bidang_studis.id', 'bidang_studis.nama_bidang_studi')
            ->orderByDesc('total')
            ->get();

        // Jika periode tidak memiliki pendaftaran, ambil data keseluruhan sebagai fallback
        if ($bidangCounts->isEmpty()) {
            $bidangCounts = DB::table('detail_pendaftaran')
                ->join('bidang_studis', 'detail_pendaftaran.bidang_studi_id', '=', 'bidang_studis.id')
                ->select('bidang_studis.nama_bidang_studi as nama', DB::raw('COUNT(detail_pendaftaran.id) as total'))
                ->groupBy('bidang_studis.id', 'bidang_studis.nama_bidang_studi')
                ->orderByDesc('total')
                ->get();
        }

        $bidangLabels = $bidangCounts->pluck('nama')->toArray();
        $bidangData = $bidangCounts->pluck('total')->map(fn($v) => (int) $v)->toArray();

        $totalMasuk = array_sum($pendapatanMasuk);
        $totalBelumMasuk = array_sum($pendapatanBelumMasuk);
        $totalTarget = array_sum($pendapatanTarget);
        if ($totalTarget !== ($totalMasuk + $totalBelumMasuk)) {
            $totalTarget = $totalMasuk + $totalBelumMasuk;
        }
        $persenRealisasi = $totalTarget > 0 ? round(($totalMasuk / $totalTarget) * 100, 1) : 0;

        $totalPiutang = array_sum($piutangData);
        $totalPendaftaran = array_sum($pendaftaranData);

        $totalSiswaPiutang = DB::table('pembayaran')
            ->whereBetween('tanggal_pembayaran', [$startStr, $endStr])
            ->where('sisa_tagihan', '>', 0)
            ->distinct()
            ->count('siswa_id');

        return [
            'pendaftaran' => [
                'title'     => 'Grafik Pendaftaran',
                'labels'    => $resLabels,
                'data'      => $pendaftaranData,
                'total'     => $totalPendaftaran,
                'max_value' => !empty($pendaftaranData) ? max($pendaftaranData) : 0,
                'avg_value' => count($pendaftaranData) > 0 ? round($totalPendaftaran / count($pendaftaranData), 1) : 0,
                'unit'      => 'Pendaftaran',
            ],
            'pendapatan'  => [
                'title'                => 'Grafik Pendapatan & Pembayaran',
                'labels'               => $resLabels,
                'masuk'                => $pendapatanMasuk,
                'belum_masuk'          => $pendapatanBelumMasuk,
                'target'               => $pendapatanTarget,
                'total_masuk'          => $totalMasuk,
                'total_belum_masuk'    => $totalBelumMasuk,
                'total_target'         => $totalTarget,
                'persentase_realisasi' => $persenRealisasi,
                'formatted_masuk'      => 'Rp ' . number_format($totalMasuk, 0, ',', '.'),
                'formatted_belum_masuk'=> 'Rp ' . number_format($totalBelumMasuk, 0, ',', '.'),
                'formatted_target'     => 'Rp ' . number_format($totalTarget, 0, ',', '.'),
                'unit'                 => 'Rupiah',
            ],
            'piutang'     => [
                'title'               => 'Grafik Piutang Siswa',
                'labels'              => $resLabels,
                'data'                => $piutangData,
                'siswa_count'         => $piutangSiswaCount,
                'total'               => $totalPiutang,
                'total_siswa_piutang' => $totalSiswaPiutang,
                'formatted_total'     => 'Rp ' . number_format($totalPiutang, 0, ',', '.'),
                'unit'                => 'Rupiah',
            ],
            'bidang_studi'=> [
                'title'     => 'Grafik Bidang Studi',
                'labels'    => $bidangLabels,
                'data'      => $bidangData,
                'total'     => array_sum($bidangData),
                'top_name'  => $bidangLabels[0] ?? '-',
                'top_count' => $bidangData[0] ?? 0,
                'unit'      => 'Pendaftaran',
            ],
        ];
    }

    /**
     * Dapatkan daftar jadwal kursus yang dikonversi ke sesi kalender per tanggal (events_by_date)
     */
    protected function getCalendarScheduleEvents(Carbon $start, Carbon $end): array
    {
        $penjadwalans = Penjadwalan::with([
            'siswa',
            'karyawan',
            'bidangStudi',
            'levelKelas',
            'detailPenjadwalan'
        ])
        ->where(function ($q) use ($start, $end) {
            $q->where('tgl_mulai', '<=', $end->toDateString())
              ->where(function ($sq) use ($start) {
                  $sq->where('tgl_selesai', '>=', $start->toDateString())
                     ->orWhereNull('tgl_selesai');
              });
        })
        ->get();

        $eventsByDate = [];

        foreach ($penjadwalans as $jadwal) {
            $jStart = Carbon::parse($jadwal->tgl_mulai)->startOfDay();
            $jEnd = $jadwal->tgl_selesai ? Carbon::parse($jadwal->tgl_selesai)->endOfDay() : null;
            $maxMeetings = $jadwal->jumlah_pertemuan ?: 999;
            $details = $jadwal->detailPenjadwalan;

            $statusText = match ((int) $jadwal->status_jadwal) {
                0 => 'Berjalan',
                1 => 'Selesai',
                2 => 'DO',
                default => 'Berjalan'
            };
            $statusBadge = match ((int) $jadwal->status_jadwal) {
                0 => 'bg-info-100 text-info-700',
                1 => 'bg-success-100 text-success-700',
                2 => 'bg-warning-100 text-warning-700',
                default => 'bg-neutral-100 text-neutral-700'
            };

            $baseItem = [
                'id'           => $jadwal->id,
                'nama_siswa'   => $jadwal->siswa->nama_siswa ?? '-',
                'bidang_studi' => ($jadwal->bidangStudi->nama_bidang_studi ?? '-') . ($jadwal->levelKelas ? ' (' . $jadwal->levelKelas->nama_level . ')' : ''),
                'pengajar'     => $jadwal->karyawan->nama_karyawan ?? '-',
                'lokasi'       => $jadwal->lokasi ?? '-',
                'status'       => $jadwal->status_jadwal,
                'status_label' => $statusText,
                'status_badge' => $statusBadge,
            ];

            if ($details->isEmpty()) {
                // Tidak ada hari berulang, tandai pada tgl_mulai jika dalam range
                if ($jStart->gte($start) && $jStart->lte($end)) {
                    $dStr = $jStart->format('Y-m-d');
                    $item = $baseItem;
                    $item['jam'] = '09:00';
                    $eventsByDate[$dStr][] = $item;
                }
            } else {
                // Penjadwalan berulang berdasarkan hari dalam detailPenjadwalan
                $daySlots = [];
                foreach ($details as $d) {
                    $dayKey = strtolower(trim((string) $d->hari));
                    if (isset($this->dayMapIndo[$dayKey])) {
                        $dow = $this->dayMapIndo[$dayKey];
                        $timeStr = !empty($d->jam_mulai) ? substr($d->jam_mulai, 0, 5) : '09:00';
                        $daySlots[$dow][] = $timeStr;
                    }
                }

                $curr = $jStart->copy();
                $meetingsCount = 0;
                $loopLimit = 365;

                while ($meetingsCount < $maxMeetings && $loopLimit-- > 0) {
                    if ($jEnd && $curr->gt($jEnd)) {
                        break;
                    }
                    if ($curr->gt($end)) {
                        break;
                    }

                    $dow = $curr->dayOfWeek;
                    if (isset($daySlots[$dow])) {
                        $meetingsCount++;
                        if ($curr->gte($start) && $curr->lte($end)) {
                            $dStr = $curr->format('Y-m-d');
                            foreach ($daySlots[$dow] as $timeStr) {
                                $item = $baseItem;
                                $item['jam'] = $timeStr;
                                $eventsByDate[$dStr][] = $item;
                            }
                        }
                    }

                    $curr->addDay();
                }
            }
        }

        // Urutkan sesi per jam pada tiap tanggal
        foreach ($eventsByDate as $dStr => &$items) {
            usort($items, fn($a, $b) => strcmp($a['jam'], $b['jam']));
        }
        unset($items);

        ksort($eventsByDate);

        return $eventsByDate;
    }

    /**
     * Hitung Top 5 Bidang Studi untuk periode tertentu
     */
    protected function getTopBidangStudi(Carbon $start, Carbon $end): array
    {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $bidangCounts = DB::table('detail_pendaftaran')
            ->join('pendaftaran', 'detail_pendaftaran.pendaftaran_id', '=', 'pendaftaran.id')
            ->join('bidang_studis', 'detail_pendaftaran.bidang_studi_id', '=', 'bidang_studis.id')
            ->whereBetween('pendaftaran.tanggal_pendaftaran', [$startStr, $endStr])
            ->select('bidang_studis.nama_bidang_studi as nama', DB::raw('COUNT(detail_pendaftaran.id) as total'))
            ->groupBy('bidang_studis.id', 'bidang_studis.nama_bidang_studi')
            ->orderByDesc('total')
            ->get();

        if ($bidangCounts->isEmpty()) {
            $bidangCounts = DB::table('detail_pendaftaran')
                ->join('bidang_studis', 'detail_pendaftaran.bidang_studi_id', '=', 'bidang_studis.id')
                ->select('bidang_studis.nama_bidang_studi as nama', DB::raw('COUNT(detail_pendaftaran.id) as total'))
                ->groupBy('bidang_studis.id', 'bidang_studis.nama_bidang_studi')
                ->orderByDesc('total')
                ->get();
        }

        $topItems = $bidangCounts->take(5);
        $maxTotal = $topItems->max('total') ?: 1;

        return [
            'items'     => $topItems,
            'max_total' => $maxTotal,
        ];
    }

    /**
     * Daftar tahun yang tersedia secara dinamis dari database
     */
    protected function getAvailableYears(): array
    {
        $now = Carbon::now();

        $yearsPendaftaran = DB::table('pendaftaran')
            ->selectRaw('DISTINCT YEAR(tanggal_pendaftaran) as yr')
            ->whereNotNull('tanggal_pendaftaran')
            ->pluck('yr');

        $yearsPembayaran = DB::table('pembayaran')
            ->selectRaw('DISTINCT YEAR(tanggal_pembayaran) as yr')
            ->whereNotNull('tanggal_pembayaran')
            ->pluck('yr');

        $yearsJadwal = DB::table('penjadwalan')
            ->selectRaw('DISTINCT YEAR(tgl_mulai) as yr')
            ->whereNotNull('tgl_mulai')
            ->pluck('yr');

        $availableYears = $yearsPendaftaran->merge($yearsPembayaran)
            ->merge($yearsJadwal)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->map(fn($y) => (int) $y)
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int) $now->year];
        }

        return $availableYears;
    }

    /**
     * Mengambil log gabungan aktivitas terbaru dari tabel Pendaftaran & Pembayaran untuk Notice Board.
     *
     * @param int $limit
     * @return array
     */
    protected function getNoticeBoardItems(int $limit = 10): array
    {
        $now = Carbon::now();

        // 1. Ambil pendaftaran terbaru
        $pendaftaranLogs = Pendaftaran::with(['siswa', 'detailPendaftaran.bidangStudi'])
            ->latest('created_at')
            ->take($limit)
            ->get();

        // 2. Ambil pembayaran terbaru
        $pembayaranLogs = Pembayaran::with(['siswa', 'detailPendaftaran.bidangStudi', 'riwayat'])
            ->latest('created_at')
            ->take($limit)
            ->get();

        $items = collect();

        foreach ($pendaftaranLogs as $p) {
            $createdAt = $p->created_at 
                ? Carbon::parse($p->created_at) 
                : ($p->tanggal_pendaftaran ? Carbon::parse($p->tanggal_pendaftaran) : $now);
            
            $siswa = $p->siswa;
            $siswaNama = $siswa->nama_siswa ?? 'Siswa';

            // Ambil nama program / bidang studi
            $programNama = $p->detailPendaftaran->map(function ($d) {
                return $d->bidangStudi->nama_bidang_studi ?? $d->bidang_studi_custom ?? null;
            })->filter()->unique()->join(', ');

            if (empty($programNama)) {
                $programNama = 'Program Kursus';
            }

            $fotoUrl = null;
            if (!empty($siswa?->foto)) {
                if (file_exists(public_path('upload/foto-siswa/' . $siswa->foto))) {
                    $fotoUrl = asset('upload/foto-siswa/' . $siswa->foto);
                } elseif (file_exists(public_path('storage/siswa/foto/' . $siswa->foto))) {
                    $fotoUrl = asset('storage/siswa/foto/' . $siswa->foto);
                } elseif (file_exists(public_path('storage/foto-siswa/' . $siswa->foto))) {
                    $fotoUrl = asset('storage/foto-siswa/' . $siswa->foto);
                }
            }

            $items->push([
                'id'           => 'reg-' . $p->id,
                'type'         => 'pendaftaran',
                'title'        => 'Pendaftaran Baru',
                'siswa_nama'   => $siswaNama,
                'program_nama' => $programNama,
                'description'  => "{$siswaNama} mendaftar ke bidang studi {$programNama}",
                'created_at'   => $createdAt,
                'time_ago'     => $this->formatRelativeTime($createdAt, $now),
                'icon'         => 'solar:document-text-bold-duotone',
                'bg_class'     => 'bg-info-100',
                'text_class'   => 'text-info-600',
                'foto_url'     => $fotoUrl,
            ]);
        }

        foreach ($pembayaranLogs as $bayar) {
            $createdAt = $bayar->created_at 
                ? Carbon::parse($bayar->created_at) 
                : ($bayar->tanggal_pembayaran ? Carbon::parse($bayar->tanggal_pembayaran) : $now);
            
            $siswa = $bayar->siswa;
            $siswaNama = $siswa->nama_siswa ?? 'Siswa';

            // Hitung nominal yang dibayarkan
            $latestRiwayat = $bayar->riwayat?->sortByDesc('created_at')->first();
            $nominal = 0;
            if ($latestRiwayat && $latestRiwayat->jumlah_bayar > 0) {
                $nominal = $latestRiwayat->jumlah_bayar;
            } elseif ($bayar->pelunasan > 0) {
                $nominal = $bayar->pelunasan;
            } elseif ($bayar->uang_muka > 0) {
                $nominal = $bayar->uang_muka;
            } elseif ($bayar->jumlah_tagihan > 0) {
                $nominal = $bayar->jumlah_tagihan - ($bayar->sisa_tagihan ?? 0);
                if ($nominal <= 0) {
                    $nominal = $bayar->jumlah_tagihan;
                }
            }

            $nominalFormatted = 'Rp ' . number_format($nominal, 0, ',', '.');
            $fotoUrl = null;
            if (!empty($siswa?->foto)) {
                if (file_exists(public_path('upload/foto-siswa/' . $siswa->foto))) {
                    $fotoUrl = asset('upload/foto-siswa/' . $siswa->foto);
                } elseif (file_exists(public_path('storage/siswa/foto/' . $siswa->foto))) {
                    $fotoUrl = asset('storage/siswa/foto/' . $siswa->foto);
                } elseif (file_exists(public_path('storage/foto-siswa/' . $siswa->foto))) {
                    $fotoUrl = asset('storage/foto-siswa/' . $siswa->foto);
                }
            }

            $items->push([
                'id'           => 'pay-' . $bayar->id,
                'type'         => 'pembayaran',
                'title'        => 'Pembayaran Masuk',
                'siswa_nama'   => $siswaNama,
                'nominal'      => $nominal,
                'description'  => "Pembayaran sebesar {$nominalFormatted} dari {$siswaNama} telah diterima",
                'created_at'   => $createdAt,
                'time_ago'     => $this->formatRelativeTime($createdAt, $now),
                'icon'         => 'solar:wallet-money-bold-duotone',
                'bg_class'     => 'bg-success-100',
                'text_class'   => 'text-success-600',
                'foto_url'     => $fotoUrl,
            ]);
        }

        // Urutkan gabungan log berdasarkan created_at desc dan ambil limit
        return $items->sortByDesc(function ($item) {
            return $item['created_at']->timestamp;
        })->take($limit)->values()->all();
    }

    /**
     * Format waktu relatif dalam bahasa Indonesia yang ramah pengguna.
     *
     * @param Carbon $date
     * @param Carbon $now
     * @return string
     */
    protected function formatRelativeTime(Carbon $date, Carbon $now): string
    {
        if ($date->isFuture()) {
            return 'Baru saja';
        }

        $diffMinutes = $date->diffInMinutes($now);

        if ($date->isToday()) {
            if ($diffMinutes < 1) {
                return 'Baru saja';
            }
            if ($diffMinutes < 60) {
                return "{$diffMinutes} menit yang lalu";
            }
            return 'Hari ini, ' . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'Kemarin, ' . $date->format('H:i');
        }

        if ($date->diffInDays($now) < 7) {
            return $date->locale('id')->diffForHumans();
        }

        return $date->translatedFormat('d M Y');
    }
}