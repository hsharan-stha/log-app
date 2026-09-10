<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'number',
        'student_id',
        'fee_type_id',
        'title',
        'amount',
        'currency',
        'issue_date',
        'due_date',
        'status',
        'notes',
        'issued_by',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'issue_date' => 'date',
            'due_date' => 'date',
            'voided_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function amountPaid(): int
    {
        return (int) $this->payments()->sum('amount');
    }

    public function balanceDue(): int
    {
        if ($this->status === 'void') {
            return 0;
        }

        return max(0, $this->amount - $this->amountPaid());
    }

    public function refreshPaymentStatus(): void
    {
        if ($this->status === 'void' || $this->status === 'draft') {
            return;
        }

        $paid = $this->amountPaid();

        if ($paid <= 0) {
            $this->forceFill(['status' => 'issued'])->save();
        } elseif ($paid >= $this->amount) {
            $this->forceFill(['status' => 'paid'])->save();
        } else {
            $this->forceFill(['status' => 'partial'])->save();
        }
    }

    public function void(): void
    {
        $this->forceFill([
            'status' => 'void',
            'voided_at' => now(),
        ])->save();
    }

    public static function nextNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';
        $latest = static::query()
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('number')
            ->value('number');

        $seq = 1;
        if (is_string($latest) && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'issued' => 'Unpaid',
            'partial' => 'Partially paid',
            'paid' => 'Paid',
            'void' => 'Void',
            default => Str::headline($this->status),
        };
    }
}
