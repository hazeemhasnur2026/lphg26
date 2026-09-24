<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    /** Nilai kolom `region` yang membedakan kantor pusat dari perwakilan. */
    public const HEADQUARTERS = 'Pusat';

    public const REPRESENTATIVE = 'Perwakilan';

    protected $fillable = [
        'name', 'address', 'phone', 'fax', 'email', 'region', 'order',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function scopeHeadquarters(Builder $query): Builder
    {
        return $query->where('region', self::HEADQUARTERS);
    }

    public function scopeRepresentative(Builder $query): Builder
    {
        return $query->where('region', self::REPRESENTATIVE);
    }

    /** Alamat multi-baris: disimpan dengan baris baru, ditampilkan dengan <br>. */
    public function addressLines(): array
    {
        return preg_split('/\R/', trim($this->address)) ?: [];
    }
}
