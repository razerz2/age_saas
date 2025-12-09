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
</div>
