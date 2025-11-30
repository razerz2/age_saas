<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciais de Acesso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .credentials-box strong {
            display: block;
            margin-bottom: 5px;
            color: #007bff;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo à Plataforma!</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $tenant->trade_name ?? $tenant->legal_name }}</strong>!</p>
            
            <p>Seu painel administrativo foi criado com sucesso.</p>
            
            <p>Acesse seu sistema através do link abaixo:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="button">Acessar Painel</a>
            </div>
            
            <p>Ou copie e cole o link no seu navegador:</p>
            <p style="word-break: break-all; color: #007bff;">{{ $loginUrl }}</p>
            
            <div class="credentials-box">
                <strong>Usuário:</strong>
                <code style="font-size: 16px;">{{ $adminEmail }}</code>
            </div>
            
            <div class="credentials-box">
                <strong>Senha:</strong>
                <code style="font-size: 16px;">{{ $adminPassword }}</code>
            </div>
            
            <div class="warning">
                <strong>⚠️ Importante:</strong> Recomendamos alterar a senha após o primeiro acesso por questões de segurança.
            </div>
            
            <p>Se você tiver alguma dúvida ou precisar de suporte, entre em contato conosco.</p>
            
            <p>Atenciosamente,<br>
            <strong>Equipe da Plataforma</strong></p>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>

