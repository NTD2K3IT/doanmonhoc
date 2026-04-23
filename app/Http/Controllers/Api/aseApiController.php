<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function success(
        string $message = 'Thành công.',
        mixed $data = null,
        int $status = 200,
        array $extra = []
    ): JsonResponse {
        $response = array_merge([
            'success' => true,
            'message' => $message,
        ], $extra);

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function error(
        string $message = 'Có lỗi xảy ra.',
        int $status = 400,
        mixed $errors = null,
        array $extra = []
    ): JsonResponse {
        $response = array_merge([
            'success' => false,
            'message' => $message,
        ], $extra);

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function created(
        string $message = 'Tạo mới thành công.',
        mixed $data = null,
        array $extra = []
    ): JsonResponse {
        return $this->success($message, $data, 201, $extra);
    }

    protected function notFound(string $message = 'Không tìm thấy dữ liệu.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function validationError(
        string $message = 'Dữ liệu không hợp lệ.',
        mixed $errors = null
    ): JsonResponse {
        return $this->error($message, 422, $errors);
    }

    protected function serverError(string $message = 'Lỗi máy chủ.'): JsonResponse
    {
        return $this->error($message, 500);
    }
}