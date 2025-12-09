<!-- Mensagem de Boas-Vindas -->
<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="color: #2c3e50; font-size: 28px; margin: 0 0 10px 0; font-weight: 600;">🎉 Bem-vindo(a)!</h1>
    <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Seu painel administrativo está pronto para uso</p>
</div>

<!-- Saudação Personalizada -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <p style="color: #ffffff; font-size: 20px; margin: 0; font-weight: 500;">
        Olá, <strong style="color: #ffffff;">{{ $tenant->trade_name ?? $tenant->legal_name }}</strong>!
    </p>
    <p style="color: rgba(255,255,255,0.95); font-size: 16px; margin: 10px 0 0 0;">
        Parabéns! Seu sistema de agendamento foi configurado com sucesso.
    </p>
</div>

<!-- Botão de Acesso Principal -->
<div style="text-align: center; margin: 35px 0;">
    <a href="{{ $loginUrl }}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 18px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
        🔐 Acessar Meu Painel
    </a>
</div>

<!-- Link Alternativo -->
<div style="background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin: 25px 0; text-align: center;">
    <p style="color: #6c757d; font-size: 14px; margin: 0 0 8px 0;">Ou copie e cole o link abaixo no seu navegador:</p>
    <p style="word-break: break-all; color: #667eea; font-size: 14px; margin: 0; font-family: 'Courier New', monospace; background-color: #ffffff; padding: 10px; border-radius: 4px; border: 1px solid #e9ecef;">
        {{ $loginUrl }}
    </p>
</div>

<!-- Credenciais de Acesso -->
<div style="background-color: #ffffff; border: 2px solid #e9ecef; border-radius: 12px; padding: 0; margin: 30px 0; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; text-align: center;">
        <h2 style="color: #ffffff; font-size: 20px; margin: 0; font-weight: 600;">🔑 Suas Credenciais de Acesso</h2>
    </div>
    
    <div style="padding: 25px;">
        <!-- Usuário -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 18px; margin-right: 8px;">👤</span>
                <strong style="color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">E-mail de Acesso</strong>
            </div>
            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; border-radius: 6px; margin-top: 8px;">
                <code style="font-size: 18px; color: #667eea; font-weight: 600; font-family: 'Courier New', monospace; background: transparent; padding: 0;">{{ $adminEmail }}</code>
            </div>
        </div>
        
        <!-- Senha -->
        <div>
            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 18px; margin-right: 8px;">🔒</span>
                <strong style="color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Senha Temporária</strong>
            </div>
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 6px; margin-top: 8px;">
                <code style="font-size: 18px; color: #856404; font-weight: 600; font-family: 'Courier New', monospace; background: transparent; padding: 0; letter-spacing: 2px;">{{ $adminPassword }}</code>
            </div>
        </div>
    </div>
</div>

<!-- Alerta de Segurança -->
<div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 5px solid #ffc107; border-radius: 8px; padding: 20px; margin: 30px 0; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.2);">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 24px; margin-right: 12px; line-height: 1;">⚠️</div>
        <div>
            <strong style="color: #856404; font-size: 16px; display: block; margin-bottom: 5px;">Importante - Segurança</strong>
            <p style="color: #856404; font-size: 14px; margin: 0; line-height: 1.6;">
                Por questões de segurança, recomendamos <strong>alterar sua senha</strong> imediatamente após o primeiro acesso ao sistema.
            </p>
        </div>
    </div>
</div>

<!-- Informações Adicionais -->
<div style="background-color: #e3f2fd; border-radius: 8px; padding: 20px; margin: 30px 0; border-left: 4px solid #2196f3;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;">💡</div>
        <div>
            <strong style="color: #1976d2; font-size: 15px; display: block; margin-bottom: 8px;">Dicas para Começar</strong>
            <ul style="color: #1565c0; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.8;">
                <li>Configure seu perfil e preferências no primeiro acesso</li>
                <li>Explore o painel para conhecer todas as funcionalidades</li>
                <li>Em caso de dúvidas, nossa equipe está pronta para ajudar</li>
            </ul>
        </div>
    </div>
</div>

<!-- Mensagem Final -->
<div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 1px solid #e9ecef;">
    <p style="color: #6c757d; font-size: 15px; margin: 0 0 10px 0; line-height: 1.6;">
        Estamos muito felizes em tê-lo(a) conosco!<br>
        Se você tiver alguma dúvida ou precisar de suporte, nossa equipe está à disposição.
    </p>
    <p style="color: #495057; font-size: 16px; margin: 20px 0 0 0;">
        <strong>Atenciosamente,</strong><br>
        <span style="color: #667eea; font-weight: 600;">Equipe da Plataforma</span>
    </p>
</div>
