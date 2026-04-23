<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCtxhSharedLogic;
use App\Http\Controllers\Controller;
use App\Models\HoatDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    use HandlesCtxhSharedLogic;

    public function events(Request $request): View
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $events = HoatDong::query()
            ->keyword($keyword)
            ->orderByDesc('thoiGianBatDau')
            ->orderByDesc('maHoatDong')
            ->paginate(10)
            ->withQueryString();

        return view('ctxh.events', compact('events', 'keyword'));
    }

    public function createEvent(): View
    {
        $statusOptions = HoatDong::statusOptions();

        return view('ctxh.events_create', compact('statusOptions'));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $this->validateEvent($request);

        $event = HoatDong::create($data);

        $this->writeActivityLog(
            'event',
            'create',
            (string) $event->maHoatDong,
            $event->tenHoatDong,
            'Thêm hoạt động mới'
        );

        return redirect()
            ->route('ctxh.events')
            ->with('success', 'Thêm hoạt động thành công.');
    }

    public function editEvent(HoatDong $hoatDong): View
    {
        $statusOptions = HoatDong::statusOptions();

        return view('ctxh.events_edit', compact('hoatDong', 'statusOptions'));
    }

    public function updateEvent(Request $request, HoatDong $hoatDong): RedirectResponse
    {
        $data = $this->validateEvent($request);

        $hoatDong->update($data);

        $this->writeActivityLog(
            'event',
            'update',
            (string) $hoatDong->maHoatDong,
            $hoatDong->tenHoatDong,
            'Cập nhật hoạt động'
        );

        return redirect()
            ->route('ctxh.events')
            ->with('success', 'Cập nhật hoạt động thành công.');
    }
}