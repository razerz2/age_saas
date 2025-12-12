<?php

namespace App\Services\WhatsApp;

interface WhatsAppProviderInterface
{
    /**
     * Envia uma mensagem de texto
     *
     * @param string $phone Número do telefone (formato: 5511999999999)
     * @param string $message Mensagem a ser enviada
     * @return bool True se enviado com sucesso, False caso contrário
     */
    public function sendMessage(string $phone, string $message): bool;

    /**
     * Formata o número de telefone para o formato esperado pela API
     *
     * @param string $phone Número do telefone
     * @return string Número formatado
     */
    public function formatPhone(string $phone): string;
}

