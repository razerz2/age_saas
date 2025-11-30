<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário Pré-Consulta</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h2 style="color: #667eea; margin-top: 0;">Formulário Pré-Consulta</h2>
        
        <p>Olá <strong>{{ $patient->full_name }}</strong>,</p>
        
        <p>Seu agendamento foi criado para o dia <strong>{{ $appointment->starts_at->format('d/m/Y \à\s H:i') }}</strong>.</p>
        
        <p>Antes da consulta, clique no link abaixo e responda o formulário:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" 
               style="display: inline-block; background-color: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Responder Formulário
            </a>
        </div>
        
        <p style="font-size: 12px; color: #666;">
            Ou copie e cole este link no seu navegador:<br>
            <a href="{{ $url }}" style="color: #667eea; word-break: break-all;">{{ $url }}</a>
        </p>
        
        <p>Obrigado!</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #666; margin: 0;">
            Esta é uma mensagem automática. Por favor, não responda este email.
        </p>
    </div>
</body>
</html>
