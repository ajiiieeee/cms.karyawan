<?php

namespace App\Exports\Sheets;

use App\Models\DetailPendaftaran;
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

class SiswaKursusSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Riwayat Kursus & Sertifikat';
    }

    public function query(): Builder
    {
        $query = DetailPendaftaran::query()
            ->with([
                'pendaftaran.siswa',
                'bidangStudi',
                'levelKelas',
                'kategoriKelas',
                'penjadwalan.karyawan',
                'penjadwalan.sertifikat'
            ]);

        // Filter jika memilih bidang studi tertentu
        if (!empty($this->filters['bidang_studi_id'])) {
            $query->where('bidang_studi_id', $this->filters['bidang_studi_id']);
        }

        // Filter status siswa jika dipilih
        if (isset($this->filters['status_siswa']) && $this->filters['status_siswa'] !== '') {
            $query->whereHas('pendaftaran.siswa', function ($q) {
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
            'No. Pendaftaran',
            'Bidang Studi',
            'Level Kelas',
            'Kategori Kelas',
            'Pengajar / Instruktur',
            'Tgl Mulai',
            'Tgl Selesai',
            'Jumlah Pertemuan',
            'Status Pelaksanaan',
            'Dokumen GBMP',
            'No. Sertifikat',
            'Tgl Terbit Sertifikat',
            'Penandatangan Sertifikat'
        ];
    }

    public function map($detail): array
    {
        $this->rowNumber++;
        $siswa = $detail->pendaftaran?->siswa;
        $jadwal = $detail->penjadwalan;
        $sertifikat = $jadwal?->sertifikat;

        // Status Pelaksanaan
        $statusJadwalText = 'Belum Terjadwal';
        if ($jadwal) {
            if ((int)$jadwal->status_jadwal === 1) {
                $statusJadwalText = 'Selesai';
            } elseif ((int)$jadwal->status_jadwal === 0) {
                $statusJadwalText = 'Berjalan';
            } elseif ((int)$jadwal->status_jadwal === 2) {
                $statusJadwalText = 'DO';
            }
        }

        // Sertifikat Siap jika selesai dan ada tanggal ttd
        $isSertifikatValid = $jadwal
            && (int)$jadwal->status_jadwal === 1
            && $sertifikat
            && $sertifikat->status === 'selesai'
            && !empty($sertifikat->signature_date);

        return [
            $this->rowNumber,
            "'" . (string)($siswa->nis ?? '-'),
            $siswa->nama_siswa ?? '-',
            $detail->no_pendaftaran ?? '-',
            $detail->bidangStudi->nama_bidang_studi ?? ($detail->bidang_studi_custom ?? '-'),
            $detail->levelKelas->nama_level ?? '-',
            $detail->kategoriKelas->nama_kategori ?? '-',
            $jadwal?->karyawan?->nama_karyawan ?? '-',
            $jadwal && $jadwal->tgl_mulai ? Carbon::parse($jadwal->tgl_mulai)->format('d/m/Y') : '-',
            $jadwal && $jadwal->tgl_selesai ? Carbon::parse($jadwal->tgl_selesai)->format('d/m/Y') : '-',
            $jadwal && $jadwal->jumlah_pertemuan ? $jadwal->jumlah_pertemuan . 'x' : '-',
            $statusJadwalText,
            !empty($jadwal?->upload_gbmp) ? 'Tersedia' : 'Belum Upload',
            $isSertifikatValid ? ($sertifikat->no_sertifikat ?? '-') : '-',
            $isSertifikatValid && $sertifikat->signature_date ? Carbon::parse($sertifikat->signature_date)->format('d/m/Y') : '-',
            $isSertifikatValid ? ($sertifikat->signature_by ?? '-') : '-'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'N' => NumberFormat::FORMAT_TEXT,
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
