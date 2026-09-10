<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KioskDevice extends Model
{
    protected $fillable = [
        'name',
        'token_hash',
        'token_prefix',
        'registered_by',
        'last_seen_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function touchLastSeen(): void
    {
        $this->forceFill(['last_seen_at' => now()])->save();
    }

    /**
     * @return array{device: self, plain_token: string}
     */
    public static function register(string $name, ?User $registrar = null): array
    {
        $plain = Str::random(64);

        $device = static::query()->create([
            'name' => $name,
            'token_hash' => hash('sha256', $plain),
            'token_prefix' => substr($plain, 0, 8),
            'registered_by' => $registrar?->id,
            'last_seen_at' => now(),
        ]);

        return ['device' => $device, 'plain_token' => $plain];
    }

    public static function findActiveByPlainToken(?string $plain): ?self
    {
        if ($plain === null || $plain === '') {
            return null;
        }

        $device = static::query()
            ->where('token_hash', hash('sha256', $plain))
            ->whereNull('revoked_at')
            ->first();

        return $device instanceof self ? $device : null;
    }
}
