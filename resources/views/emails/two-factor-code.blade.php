<!-- Mensagem de Código de Verificação -->
<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="color: #2c3e50; font-size: 28px; margin: 0 0 10px 0; font-weight: 600;">🔐 Código de Verificação</h1>
    <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Autenticação de Dois Fatores</p>
</div>

<!-- Saudação Personalizada -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <p style="color: #ffffff; font-size: 20px; margin: 0; font-weight: 500;">
        Olá, <strong style="color: #ffffff;">{{ $user->name ?? 'Usuário' }}</strong>!
    </p>
    <p style="color: rgba(255,255,255,0.95); font-size: 16px; margin: 10px 0 0 0;">
        Você solicitou um código de verificação para ativar a autenticação de dois fatores.
    </p>
</div>

<!-- Código de Verificação -->
<div style="background-color: #ffffff; border: 2px solid #e9ecef; border-radius: 12px; padding: 0; margin: 30px 0; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; text-align: center;">
        <h2 style="color: #ffffff; font-size: 20px; margin: 0; font-weight: 600;">🔑 Seu Código de Verificação</h2>
    </div>
    
    <div style="padding: 25px; text-align: center;">
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #667eea; padding: 25px; border-radius: 8px; margin: 0 auto; display: inline-block; min-width: 200px;">
            <code style="font-size: 36px; color: #667eea; font-weight: 700; font-family: 'Courier New', monospace; background: transparent; padding: 0; letter-spacing: 8px; display: block;">{{ $code }}</code>
        </div>
        <p style="color: #6c757d; font-size: 14px; margin: 20px 0 0 0;">
            Digite este código na página de configuração para ativar o 2FA
        </p>
    </div>
</div>

<!-- Alerta de Segurança -->
<div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 5px solid #ffc107; border-radius: 8px; padding: 20px; margin: 30px 0; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.2);">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 24px; margin-right: 12px; line-height: 1;">⏰</div>
        <div>
            <strong style="color: #856404; font-size: 16px; display: block; margin-bottom: 5px;">Código Válido por 10 Minutos</strong>
            <p style="color: #856404; font-size: 14px; margin: 0; line-height: 1.6;">
                Este código expira em <strong>10 minutos</strong>. Se você não solicitou este código, ignore este e-mail e verifique a segurança da sua conta.
            </p>
        </div>
    </div>
</div>

<!-- Informações Adicionais -->
<div style="background-color: #e3f2fd; border-radius: 8px; padding: 20px; margin: 30px 0; border-left: 4px solid #2196f3;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;">💡</div>
        <div>
            <strong style="color: #1976d2; font-size: 15px; display: block; margin-bottom: 8px;">Sobre a Autenticação de Dois Fatores</strong>
            <ul style="color: #1565c0; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.8;">
                <li>Aumenta significativamente a segurança da sua conta</li>
                <li>Protege seus dados mesmo se sua senha for comprometida</li>
                <li>Recomendado para todos os usuários</li>
            </ul>
        </div>
    </div>
</div>

<!-- Mensagem Final -->
<div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 1px solid #e9ecef;">
    <p style="color: #6c757d; font-size: 15px; margin: 0 0 10px 0; line-height: 1.6;">
        Se você não solicitou este código, por favor, ignore este e-mail ou entre em contato conosco imediatamente.
    </p>
    <p style="color: #495057; font-size: 16px; margin: 20px 0 0 0;">
        <strong>Atenciosamente,</strong><br>
        <span style="color: #667eea; font-weight: 600;">Equipe da Plataforma</span>
    </p>
</div>

