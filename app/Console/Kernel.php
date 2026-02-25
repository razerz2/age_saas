<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Carrega configurações de comandos do banco de dados
        $schedule->command('appointments:expire-pending')->everyFiveMinutes();
        $schedule->command('appointments:expire-waitlist-offers')->everyFiveMinutes();
        $this->scheduleCommands($schedule);
    }

    /**
     * Agenda comandos baseado nas configurações do banco de dados
     */
    private function scheduleCommands(Schedule $schedule): void
    {
        // Comandos padrão do sistema
        $defaultCommands = [
            [
                'command' => 'subscriptions:subscriptions-process',
                'default_time' => '01:00',
                'frequency' => 'daily',
            ],
            [
                'command' => 'invoices:generate',
                'default_time' => '01:30',
                'frequency' => 'daily',
            ],
            [
                'command' => 'invoices:notify-upcoming',
                'default_time' => '01:45',
                'frequency' => 'daily',
            ],
            [
                'command' => 'invoices:invoices-check-overdue',
                'default_time' => '02:00',
                'frequency' => 'daily',
            ],
            [
                'command' => 'subscriptions:process-recovery',
                'default_time' => '02:30',
                'frequency' => 'daily',
            ],
            [
                'command' => 'tenants:purge-canceled',
                'default_time' => '03:00',
                'frequency' => 'daily',
            ],
            [
                'command' => 'recurring-appointments:process',
                'default_time' => '03:00',
                'frequency' => 'daily',
            ],
            [
                'command' => 'google-calendar:renew-recurring-events',
                'default_time' => '04:00',
                'default_day' => 1,
                'frequency' => 'monthly',
            ],
            [
                'command' => 'appointments:notify-upcoming',
                'default_time' => '08:00',
                'frequency' => 'daily',
            ],
        ];

        // Carrega comandos customizados do banco
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];
        
        // Converte formato de comandos customizados para o formato esperado
        $customCommandsFormatted = [];
        foreach ($customCommands as $cmd) {
            $customCommandsFormatted[] = [
                'command' => $cmd['key'],
                'default_time' => $cmd['default_time'],
                'frequency' => $cmd['frequency'],
                'default_day' => $cmd['default_day'] ?? null,
            ];
        }

        // Merge: comandos padrão + comandos customizados
        $allCommands = array_merge($defaultCommands, $customCommandsFormatted);

        foreach ($allCommands as $cmd) {
            $commandKey = $cmd['command'];
            
            // Verifica se o comando está habilitado (padrão: habilitado)
            $enabled = sysconfig("commands.{$commandKey}.enabled", '1') === '1';
            
            if (!$enabled) {
                continue; // Pula comandos desabilitados
            }

            // Obtém horário configurado ou usa padrão
            $time = sysconfig("commands.{$commandKey}.time", $cmd['default_time']);
            
            // Agenda o comando baseado na frequência
            if ($cmd['frequency'] === 'daily') {
                $schedule->command($commandKey)->dailyAt($time);
            } elseif ($cmd['frequency'] === 'monthly') {
                $day = (int) sysconfig("commands.{$commandKey}.day", $cmd['default_day'] ?? 1);
                $schedule->command($commandKey)->monthlyOn($day, $time);
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

