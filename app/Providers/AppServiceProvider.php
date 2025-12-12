<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Mail\MailManager;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\RecurringAppointment;
use App\Observers\AppointmentObserver;
use App\Observers\RecurringAppointmentObserver;
use App\Channels\WhatsAppChannel;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Intercepta a criação do Transport SMTP para configurar SSL
        $this->app->afterResolving(MailManager::class, function ($mailManager) {
            $mailManager->extend('smtp', function ($config) use ($mailManager) {
                // Usa o método padrão do Laravel para criar o Transport
                // Isso garante que todas as configurações sejam aplicadas corretamente
                $reflection = new \ReflectionClass($mailManager);
                $method = $reflection->getMethod('createSmtpTransport');
                $method->setAccessible(true);
                $transport = $method->invoke($mailManager, $config);
                
                // Configura SSL se necessário
                if ($transport instanceof SmtpTransport) {
                    $this->configureSmtpSsl($transport);
                }
                
                return $transport;
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configura o contexto SSL para SMTP quando necessário
        $this->configureMailSsl();

        // Registra o canal customizado WhatsApp
        Notification::extend('whatsapp', function ($app) {
            return new WhatsAppChannel($app->make(\App\Services\WhatsAppService::class));
        });

        // Registra os Observers para sincronização automática com Google Calendar
        Appointment::observe(AppointmentObserver::class);
        RecurringAppointment::observe(RecurringAppointmentObserver::class);

        // Compartilha o tenant atual com todas as views
        View::composer('*', function ($view) {
            $tenant = \Spatie\Multitenancy\Models\Tenant::current();
            if ($tenant) {
                $view->with('currentTenant', $tenant);
            }
        });

        // Helper para rotas do portal do paciente que sempre inclui o tenant
        Route::macro('patientRoute', function ($name, $parameters = []) {
            $tenant = \Spatie\Multitenancy\Models\Tenant::current();
            $slug = $tenant?->subdomain;
            
            // Tenta obter do route apenas se houver uma requisição HTTP disponível
            if (!$slug && app()->runningInConsole() === false && request() !== null) {
                $slug = request()->route('slug') ?? request()->route('tenant');
            }
            
            if ($slug) {
                $parameters['slug'] = $slug;
            }
            
            return route('patient.' . $name, $parameters);
        });
    }


    /**
     * Configura o SSL no Transport SMTP
     */
    protected function configureSmtpSsl(SmtpTransport $transport): void
    {
        $verifyPeer = filter_var(env('MAIL_VERIFY_PEER', 'false'), FILTER_VALIDATE_BOOLEAN);
        $verifyPeerName = filter_var(env('MAIL_VERIFY_PEER_NAME', 'false'), FILTER_VALIDATE_BOOLEAN);

        if (!$verifyPeer || !$verifyPeerName) {
            // Usa reflexão para acessar o stream e configurar SSL
            $reflection = new \ReflectionClass($transport);
            
            // Tenta acessar a propriedade stream diretamente
            if ($reflection->hasProperty('stream')) {
                $streamProperty = $reflection->getProperty('stream');
                $streamProperty->setAccessible(true);
                $stream = $streamProperty->getValue($transport);
            } elseif ($reflection->hasMethod('getStream')) {
                $method = $reflection->getMethod('getStream');
                $method->setAccessible(true);
                $stream = $method->invoke($transport);
            } else {
                return; // Não foi possível acessar o stream
            }
            
            if ($stream instanceof SocketStream) {
                $streamReflection = new \ReflectionClass($stream);
                
                // Tenta configurar streamOptions
                if ($streamReflection->hasProperty('streamOptions')) {
                    $property = $streamReflection->getProperty('streamOptions');
                    $property->setAccessible(true);
                    $options = $property->getValue($stream) ?? [];
                    
                    $options['ssl'] = array_merge($options['ssl'] ?? [], [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]);
                    
                    $property->setValue($stream, $options);
                }
                
                // Também tenta configurar via setStreamOptions se o método existir
                if ($streamReflection->hasMethod('setStreamOptions')) {
                    $method = $streamReflection->getMethod('setStreamOptions');
                    $method->setAccessible(true);
                    $method->invoke($stream, [
                        'ssl' => [
                            'allow_self_signed' => true,
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Configura o contexto SSL para conexões SMTP (método legado)
     */
    protected function configureMailSsl(): void
    {
        // Mantido para compatibilidade, mas a configuração real é feita em configureSmtpSsl
    }
}
