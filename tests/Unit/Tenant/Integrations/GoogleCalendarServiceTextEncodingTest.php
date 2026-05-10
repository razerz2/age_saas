<?php

declare(strict_types=1);

use App\Models\Tenant\Appointment;
use App\Services\Tenant\GoogleCalendarService;
use Carbon\Carbon;
it('buildEvent does not generate mojibake markers in summary and description', function () {
    $service = new class extends GoogleCalendarService {
        public function exposeBuildEvent(Appointment $appointment)
        {
            return $this->buildEvent($appointment);
        }
    };

    $appointment = new class extends Appointment {
        public $patient;
        public $calendar;
        public $type;
        public $specialty;
        public $starts_at;
        public $ends_at;
        public $status;
        public $notes;

        public function relationLoaded($key): bool
        {
            return true;
        }

        public function load($relations)
        {
            return $this;
        }
    };

    $appointment->setRawAttributes(['id' => 123], true);

    $appointment->starts_at = Carbon::parse('2026-05-10 10:00:00');
    $appointment->ends_at = Carbon::parse('2026-05-10 10:45:00');
    $appointment->status = 'scheduled';
    $appointment->notes = 'Sem observacoes extras';

    $appointment->patient = (object) [
        'full_name' => 'Maria Silva',
        'phone' => '65999998888',
        'email' => 'maria@example.com',
        'cpf' => '123.456.789-00',
    ];

    $appointment->type = (object) [
        'name' => 'Primeira Consulta',
        'duration_min' => 45,
    ];

    $appointment->specialty = (object) [
        'name' => 'Cardiologia',
    ];

    $appointment->calendar = (object) [
        'doctor' => (object) [
            'user' => (object) [
                'name' => 'Dr. Joao',
                'name_full' => 'Dr. Joao Souza',
            ],
        ],
    ];

    $event = $service->exposeBuildEvent($appointment);

    $summary = (string) $event->getSummary();
    $description = (string) $event->getDescription();

    foreach (["\u{00C3}", "\u{00C2}", "\u{0192}"] as $marker) {
        expect($summary)->not->toContain($marker);
        expect($description)->not->toContain($marker);
    }
});
