<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $fillable = ['karyawan_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'keperluan', 'status', 'catatan_approval'];
    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}
