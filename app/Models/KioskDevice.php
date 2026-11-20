<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KioskDevice extends Model
{
    /** @var array<string, array{audience: string, location: string, label: string}> */
    public const SLOTS = [
        'teachers' => ['audience' => 'teachers', 'location' => 'school', 'label' => 'Teachers'],
        'students_school' => ['audience' => 'students', 'location' => 'school', 'label' => 'Students at school'],
        'students_bus' => ['audience' => 'students', 'location' => 'bus', 'label' => 'Students on the bus'],
    ];

    protected $fillable = [
        'name',
        'location',
        'audience',
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

    public function isBusKiosk(): bool
    {
        return $this->location === 'bus';
    }

    public function isSchoolKiosk(): bool
    {
        return $this->location !== 'bus';
    }

    public function locationLabel(): string
    {
        return $this->isBusKiosk() ? 'Bus' : 'School';
    }

    public function audienceLabel(): string
    {
        return match ($this->audience) {
            'teachers' => 'Teachers',
            'students' => 'Students',
            default => 'Teachers & students',
        };
    }

    public function blocksSlot(string $slot): bool
    {
        if (! $this->isActive() || ! isset(self::SLOTS[$slot])) {
            return false;
        }

        $wanted = self::SLOTS[$slot];

        if ($this->audience === $wanted['audience'] && $this->location === $wanted['location']) {
            return true;
        }

        if ($this->audience === null && $this->isBusKiosk()) {
            return $slot === 'students_bus';
        }

        return $this->audience === null
            && $this->location === 'school'
            && $wanted['location'] === 'school';
    }

    public static function deviceBlockingSlot(string $slot): ?self
    {
        $device = static::query()
            ->whereNull('revoked_at')
            ->get()
            ->first(fn (self $device) => $device->blocksSlot($slot));

        return $device instanceof self ? $device : null;
    }

    /**
     * @return list<string>
     */
    public static function openSlots(): array
    {
        return array_values(array_filter(
            array_keys(self::SLOTS),
            fn (string $slot) => static::deviceBlockingSlot($slot) === null
        ));
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
    public static function register(string $name, string $location = 'school', ?User $registrar = null, ?string $audience = null): array
    {
        $plain = Str::random(64);
        $location = $location === 'bus' ? 'bus' : 'school';
        $audience = in_array($audience, ['teachers', 'students'], true) ? $audience : null;
        if ($audience === 'teachers') {
            $location = 'school';
        }

        $device = static::query()->create([
            'name' => $name,
            'location' => $location,
            'audience' => $audience,
            'token_hash' => hash('sha256', $plain),
            'token_prefix' => substr($plain, 0, 8),
            'registered_by' => $registrar?->id,
            'last_seen_at' => now(),
        ]);

        return ['device' => $device, 'plain_token' => $plain];
    }

    public static function active(): ?self
    {
        $device = static::query()->whereNull('revoked_at')->latest('id')->first();

        return $device instanceof self ? $device : null;
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
