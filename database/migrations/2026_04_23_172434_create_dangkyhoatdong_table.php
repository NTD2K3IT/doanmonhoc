<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dangkyhoatdong', function (Blueprint $table) {
            $table->id();
            $table->string('maSV', 50);
            $table->unsignedBigInteger('maHoatDong');
            $table->dateTime('thoiGianDangKy')->nullable();
            $table->string('trangThai', 30)->default('registered');
            $table->timestamps();

            $table->unique(['maSV', 'maHoatDong'], 'uk_dangky_sv_hd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dangkyhoatdong');
    }
};