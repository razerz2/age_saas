<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\WhatsAppBot\DTO\InboundProcessingResult;
use App\Services\Tenant\WhatsAppBot\WhatsAppBotInboundMessageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppBotWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $provider,
        WhatsAppBotInboundMessageProcessor $processor
    ): JsonResponse {
        $result = $processor->process($provider, (array) $request->all());

        $statusCode = match ($result->status) {
            InboundProcessingResult::STATUS_PROCESSED => 200,
            InboundProcessingResult::STATUS_IGNORED => 202,
            default => 422,
        };

        return response()->json($result->toArray(), $statusCode);
    }
}

