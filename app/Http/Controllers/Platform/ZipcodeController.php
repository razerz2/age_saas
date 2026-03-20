<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\Platform\Exceptions\ZipcodeLookupException;
use App\Services\Platform\ZipcodeLookupService;
use Illuminate\Http\JsonResponse;

class ZipcodeController extends Controller
{
    public function __construct(
        private readonly ZipcodeLookupService $zipcodeLookupService
    ) {
    }

    public function show(string $zipcode): JsonResponse
    {
        try {
            $payload = $this->zipcodeLookupService->lookup($zipcode);

            return response()->json($payload);
        } catch (ZipcodeLookupException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => $exception->errorCode(),
            ], $exception->statusCode());
        }
    }
}
