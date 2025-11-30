<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruções para Consulta Online</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
            border-radius: 4px;
        }
        .link-box {
            background-color: #e3f2fd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .link-box a {
            color: #1976d2;
            word-break: break-all;
            text-decoration: none;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Consulta Online Agendada</h1>
    </div>
    
    <div class="content">
        <p>Olá <strong>{{ $patient_name }}</strong>,</p>
        
        <p>Sua consulta ONLINE foi agendada com sucesso!</p>
        
        <div class="info-box">
            <strong>📅 Data:</strong> {{ $appointment_date }}<br>
            <strong>🕐 Horário:</strong> {{ $appointment_time }}
        </div>
        
        @if(!empty($meeting_link))
        <div class="link-box">
            <strong>🔗 Link da Reunião:</strong><br>
            <a href="{{ $meeting_link }}" target="_blank">{{ $meeting_link }}</a>
        </div>
        @endif
        
        @if(!empty($meeting_app))
        <div class="info-box">
            <strong>📱 Aplicativo:</strong> {{ $meeting_app }}
        </div>
        @endif
        
        @if(!empty($general_instructions))
        <div class="info-box">
            <strong>📋 Instruções:</strong><br>
            {!! nl2br(e($general_instructions)) !!}
        </div>
        @endif
        
        @if(!empty($patient_instructions))
        <div class="info-box">
            <strong>💬 Observações:</strong><br>
            {!! nl2br(e($patient_instructions)) !!}
        </div>
        @endif
        
        <p style="margin-top: 20px;">
            <strong>Importante:</strong> Certifique-se de estar em um local tranquilo e com boa conexão de internet no horário agendado.
        </p>
    </div>
    
    <div class="footer">
        <p>Este é um email automático. Por favor, não responda.</p>
    </div>
</body>
</html>

