<?php

namespace App\Http\Requests\Tenant\CalendarSync;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarSyncStateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'external_event_id' => ['nullable', 'string', 'max:255'],
            'provider'         => ['required', 'in:google,apple'],
            'last_sync_at'     => ['nullable', 'date'],
        ];
    }
}
