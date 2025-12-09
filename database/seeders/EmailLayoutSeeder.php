<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\EmailLayout;

class EmailLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se já existe um layout
        if (EmailLayout::count() > 0) {
            $this->command->info('Layouts de email já existem. Pulando criação...');
            return;
        }

        EmailLayout::create([
            'name' => 'default',
            'display_name' => 'Layout Padrão',
            'logo_url' => null,
            'logo_width' => 200,
            'logo_height' => null,
            'header' => '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; text-align: center;">
    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">{{app_name}}</h1>
    <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Sistema de Agendamento Profissional</p>
</div>',
            'footer' => '<div style="background-color: #f8f9fa; padding: 25px 20px; text-align: center; border-top: 1px solid #e0e0e0; margin-top: 30px;">
    <p style="color: #666666; font-size: 13px; margin: 8px 0; line-height: 1.5;">
        © ' . date('Y') . ' <strong>{{app_name}}</strong>. Todos os direitos reservados.
    </p>
    <p style="color: #999999; font-size: 11px; margin: 8px 0;">
        Esta é uma mensagem automática. Por favor, não responda este email.
    </p>
    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
        <p style="color: #999999; font-size: 10px; margin: 0;">
            Se você não solicitou esta notificação, pode ignorá-la com segurança.
        </p>
    </div>
</div>',
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'background_color' => '#f8f9fa',
            'text_color' => '#333333',
            'is_active' => true,
        ]);

        $this->command->info('✅ Layout de email padrão criado com sucesso!');
    }
}
