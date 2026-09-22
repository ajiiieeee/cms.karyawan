<?php

namespace App\Exports;

use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Enumerable; // 1. Tambahkan import ini
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PendaftaranExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    private $no = 0;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    /**
     * @return Enumerable
     */
    public function collection(): Enumerable // 2. Tambahkan type hint : Enumerable di sini
    {
        $query = Siswa::whereHas('pendaftaran')
            ->with([
                'pendaftaran' => function ($q) {
                    $q->latest()->with('detailPendaftaran');
                }
            ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama_siswa', 'like', "%{$this->search}%")
                  ->orWhere('nik', 'like', "%{$this->search}%")
                  ->orWhere('no_telepon', 'like', "%{$this->search}%");
            });
        }

        return $query->latest('id')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'NIK',
            'Nama Siswa',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'No Telepon',
            'Email',
            'Alamat',
            'Tempat Daftar',
            'Tanggal Pendaftaran',
        ];
    }

    public function map($siswa): array
    {
        $this->no++;
        $latestPendaftaran = $siswa->pendaftaran->first();
        $tempatDaftar = $latestPendaftaran?->detailPendaftaran->first()->tempat_daftar ?? '-';
        
        $tanggalPendaftaran = '-';
        if ($latestPendaftaran && $latestPendaftaran->tanggal_pendaftaran) {
            $tanggalPendaftaran = Carbon::parse($latestPendaftaran->tanggal_pendaftaran)->format('d/m/Y');
        }

        $tanggalLahir = '-';
        if ($siswa->tanggal_lahir) {
            $tanggalLahir = Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y');
        }

        $jk = $siswa->jenis_kelamin === 'laki_laki' ? 'Laki-laki' : 'Perempuan';

        return [
            $this->no,
            $siswa->nis ?? '-',
            "'" . ($siswa->nik ?? '-'),
            $siswa->nama_siswa ?? '-',
            $jk,
            $siswa->tempat_lahir ?? '-',
            $tanggalLahir,
            $siswa->no_telepon ?? '-',
            $siswa->email ?? '-',
            $siswa->alamat ?? '-',
            $tempatDaftar,
            $tanggalPendaftaran,
        ];
    }

    public function styles(Worksheet $sheet): array
{
    return [
        1 => ['font' => ['bold' => true]],
    ];
}
}