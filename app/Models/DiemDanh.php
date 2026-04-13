<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    protected $table = 'diemdanh';

    public $timestamps = false;

    protected $fillable = [
        'maSV',
        'maHoatDong',
        'ngayDiemDanh',
        'thoiGianDiemDanh',
        'trangThai',
    ];

    protected $casts = [
        'ngayDiemDanh' => 'date',
        'thoiGianDiemDanh' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'maSV', 'maSV');
    }

    public function event()
    {
        return $this->belongsTo(HoatDong::class, 'maHoatDong', 'maHoatDong');
    }
}