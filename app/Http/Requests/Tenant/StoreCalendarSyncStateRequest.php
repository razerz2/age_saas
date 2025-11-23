<?php

namespace App\Http\Requests\Tenant\CalendarSync;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarSyncStateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'appointment_id'   => ['required', 'exists:appointments,id'],
            'external_event_id' => ['nullable', 'string', 'max:255'],
            'provider'         => ['required', 'in:google,apple'],
            'last_sync_at'     => ['nullable', 'date'],
        ];
    }
}
