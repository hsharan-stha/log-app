<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_date',
        'checkin_time',
        'checkout_time',
        'checkin_photo_path',
        'checkout_photo_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'checkin_time' => 'datetime',
            'checkout_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkinPhotoUrl(): ?string
    {
        return $this->photoPublicUrl($this->checkin_photo_path);
    }

    public function checkoutPhotoUrl(): ?string
    {
        return $this->photoPublicUrl($this->checkout_photo_path);
    }

    private function photoPublicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }
}
