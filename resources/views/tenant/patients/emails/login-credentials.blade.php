<div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #ddd;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Portal do Paciente</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0;">Credenciais de Acesso</p>
    </div>

    <div style="background: white; padding: 30px;">
        <p style="font-size: 16px;">Olá, <strong>{{ $patient->full_name }}</strong>!</p>
        
        <p>Suas credenciais de acesso ao portal do paciente foram criadas. Utilize as Informações abaixo para fazer login:</p>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">E-mail:</h3>
            <p style="font-size: 18px; font-weight: bold; margin: 0;">{{ $login->email }}</p>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #667eea;">Senha:</h3>
            <p style="font-size: 18px; font-weight: bold; margin: 0; font-family: monospace;">{{ $password }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $portalUrl }}" 
               style="display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                Acessar Portal do Paciente
            </a>
        </div>

        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;">
                <strong>Importante:</strong> Por segurança, altere sua senha após o primeiro acesso.
            </p>
        </div>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

        <p style="color: #666; font-size: 14px; margin: 0;">
            Atenciosamente,<br>
            <strong>{{ $tenantName }}</strong>
        </p>
    </div>
</div>

