<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class SmtpTransportWithSslFix extends SmtpTransport
{
    protected function getStream(): SocketStream
    {
        $stream = parent::getStream();
        
        // Configura o contexto SSL para ignorar erros de certificado quando necessário
        $verifyPeer = filter_var(env('MAIL_VERIFY_PEER', 'false'), FILTER_VALIDATE_BOOLEAN);
        $verifyPeerName = filter_var(env('MAIL_VERIFY_PEER_NAME', 'false'), FILTER_VALIDATE_BOOLEAN);
        
        if (!$verifyPeer || !$verifyPeerName) {
            // Usa reflexão para acessar a propriedade streamOptions privada
            $reflection = new \ReflectionClass($stream);
            
            if ($reflection->hasProperty('streamOptions')) {
                $property = $reflection->getProperty('streamOptions');
                $property->setAccessible(true);
                $options = $property->getValue($stream) ?? [];
                
                $options['ssl'] = array_merge($options['ssl'] ?? [], [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]);
                
                $property->setValue($stream, $options);
            }
        }
        
        return $stream;
    }
}

