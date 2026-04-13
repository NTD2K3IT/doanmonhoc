<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HoatDong extends Model
{
    protected $table = 'hoatdong';

    protected $primaryKey = 'maHoatDong';

    public $timestamps = false;

    public const STATUS_MO = 'Mở';
    public const STATUS_DONG = 'Đóng';
    public const STATUS_TAM_HOAN = 'Tạm hoãn';

    protected $fillable = [
        'tenHoatDong',
        'moTa',
        'diemCong',
        'maQR',
        'thoiGianBatDau',
        'thoiGianKetThuc',
        'trangThai',
    ];

    protected $casts = [
        'diemCong' => 'integer',
        'thoiGianBatDau' => 'datetime',
        'thoiGianKetThuc' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_MO,
            self::STATUS_DONG,
            self::STATUS_TAM_HOAN,
        ];
    }

    public function scopeKeyword(Builder $query, ?string $keyword): Builder
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($keyword) {
            $subQuery
                ->where('tenHoatDong', 'like', '%' . $keyword . '%')
                ->orWhere('maQR', 'like', '%' . $keyword . '%')
                ->orWhere('moTa', 'like', '%' . $keyword . '%');
        });
    }

    public function getTrangThaiBadgeClassAttribute(): string
    {
        return match ($this->trangThai) {
            self::STATUS_MO => 'status-text-pass',
            self::STATUS_TAM_HOAN => 'status-text-warning',
            default => 'status-text-fail',
        };
    }
}