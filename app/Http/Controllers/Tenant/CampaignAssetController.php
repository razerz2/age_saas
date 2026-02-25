<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CampaignAssetController extends Controller
{
    private const MAX_UPLOAD_KB = 20480; // 20 MB

    /**
     * Upload de asset para campanhas (tenant).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'max:' . self::MAX_UPLOAD_KB],
            'kind' => ['required', 'in:email_attachment,whatsapp_image,whatsapp_video,whatsapp_document,whatsapp_audio'],
            'channel' => ['nullable', 'in:email,whatsapp'],
        ], [
            'file.required' => 'Selecione um arquivo para upload.',
            'file.file' => 'Arquivo inválido.',
            'file.max' => 'O arquivo excede o limite de 20MB.',
            'kind.required' => 'Tipo de upload é obrigatório.',
            'kind.in' => 'Tipo de upload inválido.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $file = $request->file('file');
            if (!$file) {
                return;
            }

            $kind = (string) $request->input('kind', '');
            $mime = strtolower((string) $file->getMimeType());
            $originalName = (string) $file->getClientOriginalName();
            $extension = strtolower((string) $file->getClientOriginalExtension());

            if (!$this->isAllowedForKind($kind, $mime, $extension)) {
                $validator->errors()->add('file', 'Tipo de arquivo não permitido para o canal selecionado.');
            }

            if ($kind === 'email_attachment' && $this->isBlockedExecutable($mime, $extension)) {
                $validator->errors()->add('file', 'Arquivo bloqueado por segurança para anexos de email.');
            }

            if (trim($originalName) === '') {
                $validator->errors()->add('file', 'Nome de arquivo inválido.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Não foi possível concluir o upload.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadedFile = $request->file('file');
        $kind = (string) $request->input('kind');
        $channel = $this->resolveChannel($kind, (string) $request->input('channel', ''));
        $slug = (string) ($request->route('slug') ?: tenant()?->subdomain ?: 'default');
        $safeSlug = trim((string) preg_replace('/[^a-z0-9._-]+/i', '-', $slug), '-');
        if ($safeSlug === '') {
            $safeSlug = 'default';
        }

        $extension = strtolower((string) ($uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin'));
        $safeExtension = preg_replace('/[^a-z0-9]+/i', '', $extension) ?: 'bin';

        $filename = Str::random(40) . '.' . $safeExtension;
        $directory = sprintf('tenant/%s/campaigns/%s/%s', $safeSlug, now()->format('Y'), now()->format('m'));
        $disk = 'tenant_uploads';
        $storedPath = $uploadedFile->storeAs($directory, $filename, $disk);

        if (!$storedPath) {
            return response()->json([
                'message' => 'Falha ao armazenar o arquivo. Tente novamente.',
            ], 500);
        }

        $checksumSha256 = null;
        try {
            $realPath = $uploadedFile->getRealPath();
            if (is_string($realPath) && $realPath !== '' && is_file($realPath)) {
                $checksumSha256 = hash_file('sha256', $realPath) ?: null;
            }
        } catch (\Throwable $exception) {
            $checksumSha256 = null;
        }

        try {
            $asset = Asset::create([
                'disk' => $disk,
                'path' => $storedPath,
                'filename' => (string) $uploadedFile->getClientOriginalName(),
                'mime' => (string) $uploadedFile->getMimeType(),
                'size' => (int) $uploadedFile->getSize(),
                'checksum_sha256' => $checksumSha256,
                'meta_json' => [
                    'origin' => 'campaigns',
                    'kind' => $kind,
                    'channel' => $channel,
                    'slug' => $safeSlug,
                ],
                'created_by' => auth('tenant')->id() ?? auth()->id(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($storedPath);

            return response()->json([
                'message' => 'Falha ao registrar asset da campanha.',
            ], 500);
        }

        return response()->json([
            'asset_id' => $asset->id,
            'filename' => $asset->filename,
            'mime' => $asset->mime,
            'size' => (int) $asset->size,
            'url' => null,
        ], 201);
    }

    private function resolveChannel(string $kind, string $requestedChannel): string
    {
        if (str_starts_with($kind, 'email_')) {
            return 'email';
        }

        if (str_starts_with($kind, 'whatsapp_')) {
            return 'whatsapp';
        }

        return $requestedChannel !== '' ? $requestedChannel : 'email';
    }

    private function isAllowedForKind(string $kind, string $mime, string $extension): bool
    {
        if ($mime === '') {
            return false;
        }

        return match ($kind) {
            'whatsapp_image' => str_starts_with($mime, 'image/'),
            'whatsapp_video' => str_starts_with($mime, 'video/'),
            'whatsapp_audio' => str_starts_with($mime, 'audio/'),
            'whatsapp_document' => str_starts_with($mime, 'application/') || str_starts_with($mime, 'text/'),
            'email_attachment' => !$this->isBlockedExecutable($mime, $extension),
            default => false,
        };
    }

    private function isBlockedExecutable(string $mime, string $extension): bool
    {
        $blockedExtensions = [
            'exe', 'bat', 'cmd', 'com', 'scr', 'pif', 'cpl', 'msi', 'msp',
            'sh', 'bash', 'zsh', 'fish', 'ps1', 'vbs', 'js', 'jse', 'jar',
        ];

        if (in_array(strtolower($extension), $blockedExtensions, true)) {
            return true;
        }

        $blockedMimes = [
            'application/x-msdownload',
            'application/x-dosexec',
            'application/x-msdos-program',
            'application/x-sh',
            'application/x-shellscript',
            'text/x-shellscript',
            'application/x-bat',
            'application/x-msi',
            'application/x-executable',
            'application/java-archive',
        ];

        return in_array(strtolower($mime), $blockedMimes, true);
    }
}
