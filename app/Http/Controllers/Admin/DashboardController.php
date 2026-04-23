<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HoatDong;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        $totalStudents = Student::count();
        $totalEvents = HoatDong::count();

        $activeStudents = Student::query()
            ->whereIn('trangThai', ['Đang học', 'active', 'Active', 'Hoạt động'])
            ->count();

        $openEvents = HoatDong::query()
            ->whereIn('trangThai', ['Mở', 'open', 'Open'])
            ->count();

        $stats = [
            ['title' => 'Tổng số SV', 'value' => $totalStudents, 'icon' => 'SV', 'class' => 'blue'],
            ['title' => 'Tổng số sự kiện', 'value' => $totalEvents, 'icon' => 'HD', 'class' => 'purple'],
            ['title' => 'SV đang học', 'value' => $activeStudents, 'icon' => 'OK', 'class' => 'green'],
            ['title' => 'Sự kiện đang mở', 'value' => $openEvents, 'icon' => 'ON', 'class' => 'yellow'],
        ];

        $recentStudents = ActivityLog::query()
            ->where('entity_type', 'student')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'initial' => mb_strtoupper(mb_substr($log->title ?? 'S', 0, 1)),
                    'name' => $log->title,
                    'status' => match ($log->action) {
                        'create' => 'Added',
                        'update' => 'Updated',
                        'delete' => 'Deleted',
                        default => 'Changed',
                    },
                    'meta' => $log->description,
                    'time' => optional($log->created_at)->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        $events = ActivityLog::query()
            ->where('entity_type', 'event')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'title' => $log->title,
                    'date' => optional($log->created_at)->format('d/m/Y H:i') ?? 'Chưa có',
                    'note' => $log->description ?? 'Chưa cập nhật',
                ];
            })
            ->toArray();

        return view('ctxh.dashboard', compact('stats', 'recentStudents', 'events'));
    }
}