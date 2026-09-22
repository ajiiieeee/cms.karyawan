<?php

namespace App\Exports\Sheets;

use App\Models\RiwayatPembayaran;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents; // <-- 1. Tambahkan ini
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;   // <-- 2. Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranMutasiSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Mutasi Kas Masuk';
    }

    public function query(): Builder
    {
        $query = RiwayatPembayaran::query()->with([
            'pembayaran.siswa',
            'pembayaran.detailPendaftaran.bidangStudi',
            'pembayaran.detailPendaftaran.levelKelas',
        ]);

        if (isset($this->filters['status_pembayaran']) && $this->filters['status_pembayaran'] !== '') {
            $query->whereHas('pembayaran', function ($q) {
                $q->where('status_pembayaran', $this->filters['status_pembayaran']);
            });
        }

        if (!empty($this->filters['tempat_pembayaran'])) {
            $query->where('tempat_pembayaran', $this->filters['tempat_pembayaran']);
        }

        if (!empty($this->filters['jenis_pembayaran'])) {
            $query->where('metode_pembayaran', $this->filters['jenis_pembayaran']);
        }

        if (!empty($this->filters['tgl_mulai']) && !empty($this->filters['tgl_akhir'])) {
            $query->whereBetween('tanggal_bayar', [
                Carbon::parse($this->filters['tgl_mulai'])->startOfDay(),
                Carbon::parse($this->filters['tgl_akhir'])->endOfDay(),
            ]);
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Kwitansi',
            'Tanggal Bayar',
            'No. Pendaftaran',
            'Nama Siswa',
            'Kursus',
            'Jenis Transaksi',
            'Metode Pembayaran',
            'Bank Rekening Tujuan',
            'Tempat / Cabang',
            'Nominal Masuk (Rp)',
            'Keterangan / Catatan',
            'Petugas Kasir',
        ];
    }

    public function map($riwayat): array
    {
        $this->rowNumber++;
        $pembayaran = $riwayat->pembayaran;
        $detail = $pembayaran?->detailPendaftaran;
        $kursus = ($detail?->bidangStudi?->nama_bidang_studi ?? '-') . ' - ' . ($detail?->levelKelas?->nama_level ?? '-');

        return [
            $this->rowNumber,
            "'" . (string)($riwayat->no_kwitansi ?? '-'),
            $riwayat->tanggal_bayar ? Carbon::parse($riwayat->tanggal_bayar)->format('d/m/Y') : '-',
            "'" . (string)($detail?->no_pendaftaran ?? '-'),
            $pembayaran?->siswa?->nama_siswa ?? '-',
            $kursus,
            strtoupper($riwayat->jenis_transaksi ?? '-'),
            ucfirst($riwayat->metode_pembayaran ?? '-'),
            $pembayaran?->bank_tujuan ?? '-',
            $riwayat->tempat_pembayaran ?? '-',
            (float)($riwayat->jumlah_bayar ?? 0),
            $riwayat->catatan ?? '-',
            $riwayat->created_by ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'K' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Menambahkan baris Total Kas Masuk di baris terakhir
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                // Merge kolom A sampai J untuk Label
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL PENERIMAAN KAS');

                // Formula SUM otomatis
                $sheet->setCellValue("K{$totalRow}", "=SUM(K2:K{$lastRow})");

                // Styling Total
                $sheet->getStyle("A{$totalRow}:M{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FF1E3A8A'],
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2E8F0'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF94A3B8'],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_DOUBLE,
                            'color'       => ['argb' => 'FF1E3A8A'],
                        ],
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFCBD5E1'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            },
        ];
    }
}
