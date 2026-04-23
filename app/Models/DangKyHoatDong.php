<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DangKyHoatDong extends Model
{
    protected $table = 'dangkyhoatdong';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'maSV',
        'maHoatDong',
        'thoiGianDangKy',
        'trangThai',
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