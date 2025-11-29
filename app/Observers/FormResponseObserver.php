<?php

namespace App\Observers;

use App\Models\Tenant\FormResponse;
use App\Services\TenantNotificationService;

class FormResponseObserver
{
    /**
     * Handle the FormResponse "created" event.
     */
    public function created(FormResponse $formResponse): void
    {
        // Carrega os relacionamentos necessários
        $formResponse->load(['form', 'patient']);
        
        TenantNotificationService::notifyFormResponse($formResponse);
    }
}

