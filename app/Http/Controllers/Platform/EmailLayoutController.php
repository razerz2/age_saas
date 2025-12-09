<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\EmailLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailLayoutController extends Controller
{
    public function index()
    {
        $layouts = EmailLayout::orderBy('is_active', 'desc')
            ->orderBy('display_name', 'asc')
            ->get();

        return view('platform.email_layouts.index', compact('layouts'));
    }

    public function edit(EmailLayout $emailLayout)
    {
        return view('platform.email_layouts.edit', ['layout' => $emailLayout]);
    }

    public function update(Request $request, EmailLayout $emailLayout)
    {
        $rules = [
            'display_name' => 'required|string|max:255',
            'header' => 'nullable|string',
            'footer' => 'nullable|string',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'is_active' => 'boolean',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'logo_url' => 'nullable|url|max:500',
            'logo_width' => 'nullable|integer|min:50|max:500',
            'logo_height' => 'nullable|integer|min:20|max:500',
        ];

        $request->validate($rules);

        $data = $request->only([
            'display_name',
            'header',
            'footer',
            'primary_color',
            'secondary_color',
            'background_color',
            'text_color',
            'is_active',
        ]);

        // Processar logo_width e logo_height separadamente
        // logo_width não pode ser null (tem default 200), logo_height pode ser null
        if ($request->has('logo_width') && $request->logo_width !== null && $request->logo_width !== '' && $request->logo_width !== 'null') {
            $data['logo_width'] = (int) $request->logo_width;
        }
        if ($request->has('logo_height')) {
            if ($request->logo_height === null || $request->logo_height === '' || $request->logo_height === 'null') {
                $data['logo_height'] = null; // logo_height é nullable
            } else {
                $data['logo_height'] = (int) $request->logo_height;
            }
        }

        // Processar remoção de logo
        if ($request->has('remove_logo') && $request->remove_logo) {
            $this->deleteOldLogo($emailLayout->logo_url);
            $data['logo_url'] = null;
            // Não definir logo_width e logo_height como null, pois logo_width não é nullable
            // Manter os valores atuais ou usar padrão
            unset($data['logo_width']);
            unset($data['logo_height']);
        }
        // Processar upload de logo
        elseif ($request->hasFile('logo_file')) {
            $this->deleteOldLogo($emailLayout->logo_url);
            $data['logo_url'] = $this->uploadLogo($request->file('logo_file'), $emailLayout->name);
        }
        // Processar URL externa
        elseif ($request->filled('logo_url') && $request->logo_url !== 'null') {
            $this->deleteOldLogo($emailLayout->logo_url);
            $data['logo_url'] = $request->logo_url;
        }
        // Se não foi enviado nada, manter o logo atual
        else {
            // Manter valores atuais
            if (!$request->has('logo_width')) {
                unset($data['logo_width']);
            }
            if (!$request->has('logo_height')) {
                unset($data['logo_height']);
            }
        }

        $emailLayout->update($data);

        // Se este layout foi ativado, desativa os outros
        if ($request->has('is_active') && $request->is_active) {
            EmailLayout::where('id', '!=', $emailLayout->id)
                ->update(['is_active' => false]);
        }

        return redirect()
            ->route('Platform.email-layouts.index')
            ->with('success', 'Layout atualizado com sucesso!');
    }

    protected function uploadLogo($file, $layoutName)
    {
        $storagePath = 'email-layouts/logos';
        
        if (!Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->makeDirectory($storagePath, 0755, true);
        }

        $filename = 'logo_' . Str::slug($layoutName) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($storagePath, $filename, 'public');
        
        return asset('storage/' . $path);
    }

    protected function deleteOldLogo($logoUrl)
    {
        if (!$logoUrl) {
            return;
        }

        $assetUrl = asset('storage/');
        
        if (strpos($logoUrl, $assetUrl) !== false) {
            $oldLogoPath = str_replace($assetUrl . '/', '', $logoUrl);
            if (Storage::disk('public')->exists($oldLogoPath)) {
                Storage::disk('public')->delete($oldLogoPath);
            }
        }
    }

    public function preview(EmailLayout $emailLayout)
    {
        $sampleData = [
            'app_name' => config('app.name', 'Sistema de Agendamento'),
            'subject' => 'Exemplo de Notificação',
            'content' => '<p>Olá <strong>João Silva</strong>,</p>
                <p>Este é um exemplo de como sua notificação será exibida.</p>
                <p>Você pode usar variáveis como <code>{{tenant_name}}</code>, <code>{{invoice_value}}</code>, etc.</p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="#" style="display: inline-block; background-color: ' . $emailLayout->primary_color . '; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                        Ação Principal
                    </a>
                </div>',
        ];

        $html = $this->renderLayout($emailLayout, $sampleData['content'], $sampleData);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Aplica o layout de email a qualquer conteúdo HTML
     * Método estático público para ser usado em qualquer lugar do sistema
     */
    public static function applyLayoutToContent(string $content, array $data = []): string
    {
        $layout = EmailLayout::getActiveLayout();
        $controller = new self();
        return $controller->renderLayout($layout, $content, $data);
    }

    public function renderLayout(EmailLayout $layout, string $content, array $data = []): string
    {
        // Adiciona dados do logo
        if (!isset($data['logo_url']) && $layout->logo_url) {
            $data['logo_url'] = $this->ensureAbsoluteUrl($layout->logo_url);
        }
        if (!isset($data['logo_width'])) {
            $data['logo_width'] = $layout->logo_width ?? 200;
        }
        if (!isset($data['logo_height'])) {
            $data['logo_height'] = $layout->logo_height;
        }
        if (!isset($data['app_name'])) {
            $data['app_name'] = config('app.name', 'Sistema de Agendamento');
        }

        // Processa o header
        $header = $this->processHeader($layout->header ?? '', $layout, $data);
        $footer = $this->replaceVariables($layout->footer ?? '', $data);
        
        // Substitui variáveis no conteúdo (pode ter placeholders como {{app_name}} no conteúdo)
        // Se o conteúdo já foi renderizado pela view Blade, os placeholders já foram substituídos
        // Mas ainda pode ter placeholders do layout como {{app_name}}
        $content = $this->replaceVariables($content, $data);

        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . ($data['subject'] ?? 'Notificação') . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: ' . $layout->background_color . ';">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: ' . $layout->background_color . ';">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td>' . $header . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px; color: ' . $layout->text_color . '; line-height: 1.6;">' . $content . '</td>
                    </tr>
                    <tr>
                        <td>' . $footer . '</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    protected function processHeader(string $header, EmailLayout $layout, array $data): string
    {
        if (empty($layout->logo_url)) {
            // Remove blocos condicionais
            $header = preg_replace('/@if\(logo_url\)[\s\S]*?@else([\s\S]*?)@endif/', '$1', $header);
            $header = preg_replace('/@if\(logo_url\)[\s\S]*?@endif/', '', $header);
            return $this->replaceVariables($header, $data);
        }

        $logoUrl = $this->ensureAbsoluteUrl($layout->logo_url);
        $logoWidth = $layout->logo_width ?? 200;
        $logoHeight = $layout->logo_height ? $layout->logo_height . 'px' : 'auto';

        // Processa blocos @if(logo_url)
        $header = preg_replace_callback('/@if\(logo_url\)([\s\S]*?)@else([\s\S]*?)@endif/', function($matches) use ($logoUrl, $logoWidth, $logoHeight, $data) {
            return $this->processLogoContent($matches[1], $logoUrl, $logoWidth, $logoHeight, $data);
        }, $header);

        $header = preg_replace_callback('/@if\(logo_url\)([\s\S]*?)@endif/', function($matches) use ($logoUrl, $logoWidth, $logoHeight, $data) {
            return $this->processLogoContent($matches[1], $logoUrl, $logoWidth, $logoHeight, $data);
        }, $header);

        // Substitui placeholders simples
        $header = $this->replaceLogoPlaceholders($header, $logoUrl, $logoWidth, $logoHeight, $data);

        return $this->replaceVariables($header, $data);
    }

    protected function processLogoContent(string $content, string $logoUrl, int $logoWidth, string $logoHeight, array $data): string
    {
        // Substitui [url_do_logo] e {{logo_url}} dentro de src="..." e atualiza o style
        $content = preg_replace_callback('/<img([^>]*)\s+src\s*=\s*["\'](\[url_do_logo\]|\{\{logo_url\}\})["\']([^>]*)>/i', function($matches) use ($logoUrl, $logoWidth, $logoHeight) {
            $beforeSrc = $matches[1];
            $afterSrc = $matches[3];
            
            // Novo src com URL
            $newSrc = 'src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '"';
            
            // Atualiza ou adiciona style com largura e altura
            $combinedAttrs = trim($beforeSrc . ' ' . $afterSrc);
            $styleAttr = '';
            
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $combinedAttrs, $styleMatch)) {
                $existingStyle = $styleMatch[1];
                // Remove max-width e height se existirem
                $existingStyle = preg_replace('/max-width\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = preg_replace('/height\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = trim($existingStyle, '; ');
                $styleAttr = 'style="' . htmlspecialchars($existingStyle, ENT_QUOTES);
                if ($existingStyle) $styleAttr .= '; ';
                $styleAttr .= 'max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            } else {
                $styleAttr = 'style="max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            }
            
            // Remove style antigo se existir
            $beforeSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $beforeSrc);
            $afterSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $afterSrc);
            
            return '<img' . trim($beforeSrc) . ' ' . $newSrc . ' ' . $styleAttr . ' ' . trim($afterSrc) . '>';
        }, $content);

        // Substitui outros placeholders
        $content = $this->replaceLogoPlaceholders($content, $logoUrl, $logoWidth, $logoHeight, $data);

        return $content;
    }

    protected function replaceLogoPlaceholders(string $content, string $logoUrl, int $logoWidth, string $logoHeight, array $data): string
    {
        $logoHtml = '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '" alt="' . htmlspecialchars($data['app_name'] ?? 'Logo', ENT_QUOTES) . '" style="max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;" />';

        // Substitui [url_do_logo] dentro de src (já foi processado acima, mas fazemos fallback)
        // Agora também atualiza o style
        $content = preg_replace_callback('/<img([^>]*)\s+src\s*=\s*["\'](\[url_do_logo\]|\{\{logo_url\}\})["\']([^>]*)>/i', function($matches) use ($logoUrl, $logoWidth, $logoHeight) {
            $beforeSrc = $matches[1];
            $afterSrc = $matches[3];
            
            // Novo src com URL
            $newSrc = 'src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '"';
            
            // Atualiza ou adiciona style com largura e altura
            $combinedAttrs = trim($beforeSrc . ' ' . $afterSrc);
            $styleAttr = '';
            
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $combinedAttrs, $styleMatch)) {
                $existingStyle = $styleMatch[1];
                // Remove max-width e height se existirem
                $existingStyle = preg_replace('/max-width\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = preg_replace('/height\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = trim($existingStyle, '; ');
                $styleAttr = 'style="' . htmlspecialchars($existingStyle, ENT_QUOTES);
                if ($existingStyle) $styleAttr .= '; ';
                $styleAttr .= 'max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            } else {
                $styleAttr = 'style="max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            }
            
            // Remove style antigo se existir
            $beforeSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $beforeSrc);
            $afterSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $afterSrc);
            
            return '<img' . trim($beforeSrc) . ' ' . $newSrc . ' ' . $styleAttr . ' ' . trim($afterSrc) . '>';
        }, $content);

        // Substitui [url_do_logo] fora de src pela tag completa
        $content = str_replace('[url_do_logo]', $logoHtml, $content);
        $content = str_replace('{{logo_url}}', $logoHtml, $content);

        // Se tem logo e tem {{app_name}}, substitui por logo
        if (strpos($content, '{{app_name}}') !== false) {
            $content = str_replace('{{app_name}}', $logoHtml, $content);
        }

        return $content;
    }

    protected function ensureAbsoluteUrl(string $url): string
    {
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            return 'http:' . $url;
        }

        return url($url);
    }

    protected function replaceVariables(string $content, array $data): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? $matches[0];
        }, $content);
    }
}
