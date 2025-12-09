<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailLayout extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'display_name',
        'logo_url',
        'logo_width',
        'logo_height',
        'header',
        'footer',
        'primary_color',
        'secondary_color',
        'background_color',
        'text_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($layout) {
            if (!$layout->id) {
                $layout->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Retorna o layout ativo padrão
     */
    public static function getActiveLayout(): self
    {
        return static::where('is_active', true)->first() 
            ?? static::first() 
            ?? static::createDefaultLayout();
    }

    /**
     * Cria um layout padrão se não existir nenhum
     */
    protected static function createDefaultLayout(): self
    {
        return static::create([
            'name' => 'default',
            'display_name' => 'Layout Padrão',
            'logo_url' => null,
            'logo_width' => 200,
            'logo_height' => null,
            'header' => static::getDefaultHeader(),
            'footer' => static::getDefaultFooter(),
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'background_color' => '#f8f9fa',
            'text_color' => '#333333',
            'is_active' => true,
        ]);
    }

    /**
     * Retorna o cabeçalho padrão
     */
    protected static function getDefaultHeader(): string
    {
        return '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; text-align: center;">
            @if(logo_url)
            <img src="{{logo_url}}" alt="{{app_name}}" style="max-width: 200px; height: auto; margin-bottom: 10px;" />
            @else
            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">{{app_name}}</h1>
            @endif
        </div>';
    }

    /**
     * Retorna o rodapé padrão
     */
    protected static function getDefaultFooter(): string
    {
        return '<div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e0e0e0; margin-top: 30px;">
            <p style="color: #666666; font-size: 12px; margin: 5px 0;">
                © ' . date('Y') . ' {{app_name}}. Todos os direitos reservados.
            </p>
            <p style="color: #999999; font-size: 11px; margin: 5px 0;">
                Esta é uma mensagem automática. Por favor, não responda este email.
            </p>
        </div>';
    }
}
