<?php

namespace App\Exports\Sheets;

use App\Models\Pembayaran;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Database\Eloquent\Builder;

class SiswaPembayaranSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Tagihan & Keuangan';
    }

    public function query(): Builder
    {
        $query = Pembayaran::query()
            ->with([
                'siswa',
                'detailPendaftaran.bidangStudi',
                'detailPendaftaran.levelKelas'
            ]);

        // Filter status lunas / belum lunas jika ada
        if (isset($this->filters['status_pembayaran']) && $this->filters['status_pembayaran'] !== '') {
            $query->where('status_pembayaran', $this->filters['status_pembayaran']);
        }

        // Filter status siswa jika ada
        if (isset($this->filters['status_siswa']) && $this->filters['status_siswa'] !== '') {
            $query->whereHas('siswa', function ($q) {
                $q->where('status_siswa', $this->filters['status_siswa']);
            });
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Kursus & Level',
            'Skema Pembayaran',
            'Total Tagihan (Rp)',
            'Uang Muka / DP (Rp)',
            'Tgl Pembayaran DP',
            'Pelunasan (Rp)',
            'Tgl Pelunasan',
            'Sisa Tagihan (Rp)',
            'Status Pembayaran'
        ];
    }

    public function map($bayar): array
    {
        $this->rowNumber++;
        $siswa = $bayar->siswa;
        $detail = $bayar->detailPendaftaran;
        
        $kursusNama = ($detail?->bidangStudi?->nama_bidang_studi ?? '-') . ' (' . ($detail?->levelKelas?->nama_level ?? '-') . ')';

        // Deteksi apakah skema lunas langsung atau bertahap (DP)
        $isLangsungLunas = ((float)($bayar->uang_muka ?? 0) <= 0 && $bayar->status_pembayaran)
            || ((float)($bayar->uang_muka ?? 0) >= (float)($bayar->jumlah_tagihan ?? 0));

        $pelunasanNominal = $isLangsungLunas ? (float)($bayar->jumlah_tagihan ?? 0) : (float)($bayar->pelunasan ?? 0);

        return [
            $this->rowNumber,
            "'" . (string)($siswa->nis ?? '-'),
            $siswa->nama_siswa ?? '-',
            $kursusNama,
            $isLangsungLunas ? 'Langsung Lunas' : 'Bertahap (DP)',
            (float)($bayar->jumlah_tagihan ?? 0),
            !$isLangsungLunas ? (float)($bayar->uang_muka ?? 0) : 0,
            !$isLangsungLunas && $bayar->tanggal_pembayaran ? Carbon::parse($bayar->tanggal_pembayaran)->format('d/m/Y') : '-',
            $pelunasanNominal,
            $bayar->tanggal_pelunasan ? Carbon::parse($bayar->tanggal_pelunasan)->format('d/m/Y') : ($isLangsungLunas && $bayar->tanggal_pembayaran ? Carbon::parse($bayar->tanggal_pembayaran)->format('d/m/Y') : '-'),
            (float)($bayar->sisa_tagihan ?? 0),
            $bayar->status_pembayaran ? 'Lunas' : 'Belum Lunas (DP)'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'F' => '"Rp "#,##0', // Format Rupiah Excel asli sehingga bisa di-SUM otomatis
            'G' => '"Rp "#,##0',
            'I' => '"Rp "#,##0',
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
                    'startColor' => ['argb' => 'FF1E3A8A'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}