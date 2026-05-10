<?php

namespace App\Models\Tenant;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class GoogleCalendarToken extends Model
{
    use HasFactory;

    public const ACCESS_TOKEN_ENCRYPTION_VERSION = 1;
    public const ACCESS_TOKEN_ENCRYPTED_FLAG_KEY = '__enc';
    public const ACCESS_TOKEN_ENCRYPTED_VERSION_KEY = '__enc_v';
    public const ACCESS_TOKEN_ENCRYPTED_PAYLOAD_KEY = '__enc_payload';

    protected $connection = 'tenant';
    protected $table = 'google_calendar_tokens';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'doctor_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com o médico
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Verifica se o token está expirado
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Verifica se o token está válido (não expirado)
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                $decoded = $this->decodeAccessTokenDatabaseValue($value);
                if (!is_array($decoded)) {
                    return [];
                }

                if (self::isEncryptedAccessTokenPayload($decoded)) {
                    $payload = (string) ($decoded[self::ACCESS_TOKEN_ENCRYPTED_PAYLOAD_KEY] ?? '');
                    if ($payload === '') {
                        return [];
                    }

                    try {
                        $decryptedJson = Crypt::decryptString($payload);
                    } catch (DecryptException) {
                        return [];
                    }

                    $decrypted = $this->decodeJsonArray($decryptedJson);
                    return is_array($decrypted) ? $decrypted : [];
                }

                return $decoded;
            },
            set: function (mixed $value): array {
                $normalized = $this->normalizeAccessTokenPayload($value);
                $encryptedPayload = Crypt::encryptString(
                    json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );

                return [
                    'access_token' => json_encode([
                        self::ACCESS_TOKEN_ENCRYPTED_FLAG_KEY => true,
                        self::ACCESS_TOKEN_ENCRYPTED_VERSION_KEY => self::ACCESS_TOKEN_ENCRYPTION_VERSION,
                        self::ACCESS_TOKEN_ENCRYPTED_PAYLOAD_KEY => $encryptedPayload,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];
            }
        );
    }

    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?string {
                if ($value === null) {
                    return null;
                }

                $raw = is_string($value) ? trim($value) : '';
                if ($raw === '') {
                    return null;
                }

                if (!self::looksLikeEncryptedString($raw)) {
                    return $raw;
                }

                try {
                    return Crypt::decryptString($raw);
                } catch (DecryptException) {
                    return $raw;
                }
            },
            set: function (mixed $value): array {
                if ($value === null) {
                    return ['refresh_token' => null];
                }

                $plain = trim((string) $value);
                if ($plain === '') {
                    return ['refresh_token' => null];
                }

                return ['refresh_token' => Crypt::encryptString($plain)];
            }
        );
    }

    public static function isEncryptedAccessTokenPayload(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if (($value[self::ACCESS_TOKEN_ENCRYPTED_FLAG_KEY] ?? null) !== true) {
            return false;
        }

        $payload = $value[self::ACCESS_TOKEN_ENCRYPTED_PAYLOAD_KEY] ?? null;
        return is_string($payload) && trim($payload) !== '';
    }

    public static function looksLikeEncryptedString(mixed $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $decoded = base64_decode($value, true);
        if (!is_string($decoded) || $decoded === '') {
            return false;
        }

        $payload = json_decode($decoded, true);
        if (!is_array($payload)) {
            return false;
        }

        return isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    private function decodeAccessTokenDatabaseValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        return $this->decodeJsonArray($value);
    }

    private function decodeJsonArray(string $value): ?array
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeAccessTokenPayload(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = $this->decodeJsonArray($value);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
