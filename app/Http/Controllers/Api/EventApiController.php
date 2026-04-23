<?php

namespace App\Http\Controllers\Api;

use App\Models\HoatDong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $events = HoatDong::orderByDesc('maHoatDong')->get();

        return $this->success('Lấy danh sách sự kiện thành công.', $events);
    }

    public function show(int $id): JsonResponse
    {
        $event = HoatDong::where('maHoatDong', $id)->first();

        if (!$event) {
            return $this->notFound('Không tìm thấy sự kiện.');
        }

        return $this->success('Lấy chi tiết sự kiện thành công.', $event);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'tenHoatDong' => ['required', 'string', 'max:255'],
                'ngayToChuc' => ['required', 'date'],
                'diaDiem' => ['nullable', 'string', 'max:255'],
                'moTa' => ['nullable', 'string'],
            ]);

            $event = HoatDong::create($data);

            return $this->created('Tạo sự kiện thành công.', $event);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Dữ liệu gửi lên không hợp lệ.', $e->errors());
        } catch (\Throwable $e) {
            return $this->serverError('Không thể tạo sự kiện: ' . $e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $event = HoatDong::where('maHoatDong', $id)->first();

            if (!$event) {
                return $this->notFound('Không tìm thấy sự kiện.');
            }

            $data = $request->validate([
                'tenHoatDong' => ['sometimes', 'string', 'max:255'],
                'ngayToChuc' => ['sometimes', 'date'],
                'diaDiem' => ['nullable', 'string', 'max:255'],
                'moTa' => ['nullable', 'string'],
            ]);

            $event->update($data);

            return $this->success('Cập nhật sự kiện thành công.', $event);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Dữ liệu gửi lên không hợp lệ.', $e->errors());
        } catch (\Throwable $e) {
            return $this->serverError('Không thể cập nhật sự kiện: ' . $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $event = HoatDong::where('maHoatDong', $id)->first();

            if (!$event) {
                return $this->notFound('Không tìm thấy sự kiện.');
            }

            $event->delete();

            return $this->success('Xóa sự kiện thành công.');
        } catch (\Throwable $e) {
            return $this->serverError('Không thể xóa sự kiện: ' . $e->getMessage());
        }
    }
}