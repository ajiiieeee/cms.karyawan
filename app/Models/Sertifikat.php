<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sertifikat extends Model
{
    use HasFactory;

    protected $table = 'sertifikat';

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->public_id = $model->generatePublicId();
        });
    }

public static function generatePublicId(): string
    {
        return sprintf(
            '%s-%s-%s-%s',
            date('Ymd'),
            substr(date('His'), 0, 4),
            bin2hex(random_bytes(2)),
            substr(bin2hex(random_bytes(2)), 0, 2)
        );
    }

protected $fillable = [
        'no_sertifikat',
        'tanggal',
        'nama_siswa',
        'nama_bidang_studi',
        'level',
        'tgl_mulai',
        'tgl_selesai',
        'penjadwalan_id',
        'siswa_id',
        'signature_by',
        'signature_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'        => 'date',
            'tgl_mulai'      => 'date',
            'tgl_selesai'    => 'date',
            'signature_date' => 'datetime',
        ];
    }

    public function penjadwalan()
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Generate a secure HMAC verification token for this sertifikat.
     */
    public function getVerificationToken(): string
    {
        return hash_hmac('sha256', (string) $this->id, config('app.key'));
    }

    /**
     * Find a sertifikat by its verification token (timing-safe).
     */
    public static function findByVerificationToken(string $token): ?self
    {
        return static::all()->first(function ($sertifikat) use ($token) {
            return hash_equals($sertifikat->getVerificationToken(), $token);
        });
    }

    /**
     * Get the full public verification URL for this sertifikat.
     */
    public function getVerificationUrl(): string
    {
        return route('sertifikat.verifikasi', ['token' => $this->getVerificationToken()]);
    }
}
