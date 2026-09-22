<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'detail_pendaftaran_id',
        'pendaftaran_id',
        'siswa_id',
        'jumlah_tagihan',
        'tanggal_pembayaran',
        'tanggal_pelunasan',
        'jenis_pembayaran',
        'bank_tujuan',
        'uang_muka',
        'pelunasan',
        'tempat_pembayaran',
        'sisa_tagihan',
        'status_pembayaran',
        'no_kwitansi_dp',
        'no_kwitansi_pelunasan',
        'bukti_uang_muka',
        'bukti_pelunasan',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pembayaran' => 'date',
            'tanggal_pelunasan'  => 'date',
            'jumlah_tagihan'     => 'integer',
            'uang_muka'          => 'integer',
            'pelunasan'          => 'integer',
            'sisa_tagihan'       => 'integer',
            'status_pembayaran'  => 'boolean',
        ];
    }

    public function detailPendaftaran()
    {
        return $this->belongsTo(DetailPendaftaran::class, 'detail_pendaftaran_id');
    }

    public function penjadwalan()
    {
        return $this->hasOne(Penjadwalan::class, 'pembayaran_id');
    }

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatPembayaran::class, 'pembayaran_id')->orderBy('tanggal_bayar', 'asc');
    }
}
