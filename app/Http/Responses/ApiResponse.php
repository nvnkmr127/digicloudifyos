<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, array $meta = [], int $status = 200): JsonResponse
    {
        $requestId = request()?->attributes?->get('request_id');
        if (is_string($requestId) && $requestId !== '' && ! array_key_exists('request_id', $meta)) {
            $meta['request_id'] = $requestId;
        }

        $payload = [
            'success' => true,
        ];

        if (is_string($message) && $message !== '') {
            $payload['message'] = $message;
        }

        [$normalizedData, $normalizedMeta] = self::normalizeData($data);
        $payload['data'] = $normalizedData;

        $mergedMeta = array_merge($normalizedMeta, $meta);
        if ($mergedMeta !== []) {
            $payload['meta'] = $mergedMeta;
        }

        return response()->json($payload, $status);
    }

    public static function error(string $message, array $errors = [], array $meta = [], int $status = 400): JsonResponse
    {
        $requestId = request()?->attributes?->get('request_id');
        if (is_string($requestId) && $requestId !== '' && ! array_key_exists('request_id', $meta)) {
            $meta['request_id'] = $requestId;
        }

        $payload = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function created(mixed $data = null, ?string $message = null, array $meta = []): JsonResponse
    {
        return self::success($data, $message, $meta, 201);
    }

    private static function normalizeData(mixed $data): array
    {
        if ($data instanceof JsonResource || $data instanceof AnonymousResourceCollection) {
            $arr = $data->response()->getData(true);
            if (is_array($arr) && array_key_exists('data', $arr)) {
                $meta = $arr;
                unset($meta['data']);

                return [$arr['data'], $meta];
            }

            return [$arr, []];
        }

        return [$data, []];
    }
}
