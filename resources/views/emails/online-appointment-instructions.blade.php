<div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
    <div style="background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Consulta Online Agendada</h1>
    </div>
    
    <div style="background-color: white; padding: 20px;">
        <p>Olá <strong>{{ $patient_name }}</strong>,</p>
        
        <p>Sua consulta ONLINE foi agendada com sucesso!</p>
        
        <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; border-radius: 4px;">
            <strong>📅 Data:</strong> {{ $appointment_date }}<br>
            <strong>🕐 Horário:</strong> {{ $appointment_time }}
        </div>
        
        @if(!empty($meeting_link))
        <div style="background-color: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <strong>🔗 Link da Reunião:</strong><br>
            <a href="{{ $meeting_link }}" target="_blank" style="color: #1976d2; word-break: break-all; text-decoration: none; font-weight: bold;">{{ $meeting_link }}</a>
        </div>
        @endif
        
        @if(!empty($meeting_app))
        <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; border-radius: 4px;">
            <strong>📱 Aplicativo:</strong> {{ $meeting_app }}
        </div>
        @endif
        
        @if(!empty($general_instructions))
        <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; border-radius: 4px;">
            <strong>📋 Instruções:</strong><br>
            {!! nl2br(e($general_instructions)) !!}
        </div>
        @endif
        
        @if(!empty($patient_instructions))
        <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; border-radius: 4px;">
            <strong>💬 Observações:</strong><br>
            {!! nl2br(e($patient_instructions)) !!}
        </div>
        @endif
        
        <p style="margin-top: 20px;">
            <strong>Importante:</strong> Certifique-se de estar em um local tranquilo e com boa conexão de internet no horário agendado.
        </p>
    </div>
</div>
