<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'sinhvien';

    protected $primaryKey = 'maSV';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maSV',
        'hoTen',
        'gioiTinh',
        'ngaySinh',
        'cccd',
        'diaChi',
        'soDienThoai',
        'email',
        'maLop',
        'ngayNhapHoc',
        'trangThai',
        'avatar',
    ];

    protected $casts = [
        'ngaySinh' => 'date',
        'ngayNhapHoc' => 'date',
    ];
}