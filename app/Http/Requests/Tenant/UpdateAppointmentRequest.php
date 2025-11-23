<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'calendar_id'      => ['required', 'exists:calendars,id'],
            'appointment_type' => ['nullable', 'exists:appointment_types,id'],
            'patient_id'       => ['required', 'exists:patients,id'],
            'specialty_id'     => ['nullable', 'exists:medical_specialties,id'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],

            'status'           => ['required', 'in:scheduled,rescheduled,canceled,attended,no_show'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
