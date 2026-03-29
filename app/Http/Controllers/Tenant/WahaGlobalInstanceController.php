<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\TenantWahaGlobalOperationsService;
use Illuminate\Http\JsonResponse;

class WahaGlobalInstanceController extends Controller
{
    public function __construct(
        private readonly TenantWahaGlobalOperationsService $operationsService
    ) {
    }

    public function status(): JsonResponse
    {
        $result = $this->operationsService->status(true);

        return $this->toJsonResponse($result);
    }

    public function qrCode(): JsonResponse
    {
        $result = $this->operationsService->qrCode();

        return $this->toJsonResponse($result);
    }

    public function start(): JsonResponse
    {
        $result = $this->operationsService->executeAction('start');

        return $this->toJsonResponse($result);
    }

    public function restart(): JsonResponse
    {
        $result = $this->operationsService->executeAction('restart');

        return $this->toJsonResponse($result);
    }

    public function stop(): JsonResponse
    {
        $result = $this->operationsService->executeAction('stop');

        return $this->toJsonResponse($result);
    }

    public function logout(): JsonResponse
    {
        $result = $this->operationsService->executeAction('logout');

        return $this->toJsonResponse($result);
    }

    public function bindWebhook(): JsonResponse
    {
        $result = $this->operationsService->bindWebhook();

        return $this->toJsonResponse($result);
    }

    /**
     * @param array<string, mixed> $result
     */
    private function toJsonResponse(array $result): JsonResponse
    {
        $httpStatus = (int) ($result['http_status'] ?? 200);
        if (!isset($result['ok']) || $result['ok'] !== true) {
            if ($httpStatus < 400) {
                $httpStatus = 422;
            }
        } elseif ($httpStatus < 200 || $httpStatus >= 300) {
            $httpStatus = 200;
        }

        return response()->json(
            $result,
            $httpStatus,
            [],
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}
