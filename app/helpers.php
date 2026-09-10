<?php

use App\Models\User;

if (! function_exists('face_euclidean_distance')) {
    /**
     * Euclidean distance between two face descriptor vectors (same length).
     */
    function face_euclidean_distance(array $a, array $b): float
    {
        $countA = count($a);
        $countB = count($b);
        if ($countA === 0 || $countA !== $countB) {
            return PHP_FLOAT_MAX;
        }

        $sum = 0.0;
        for ($i = 0; $i < $countA; $i++) {
            $d = (float) $a[$i] - (float) $b[$i];
            $sum += $d * $d;
        }

        return sqrt($sum);
    }
}

if (! function_exists('face_find_matching_person')) {
    /**
     * Find teacher or student whose stored descriptor is within threshold.
     *
     * @param  array<int, float>  $incomingDescriptor
     * @return array{user: User|null, distance: float|null}
     */
    function face_find_matching_person(array $incomingDescriptor, float $threshold = 0.5): array
    {
        $bestUser = null;
        $bestDistance = null;

        $people = User::query()
            ->whereIn('role', ['teacher', 'student'])
            ->whereNotNull('face_descriptor')
            ->get(['id', 'name', 'role', 'face_descriptor']);

        foreach ($people as $user) {
            $stored = $user->face_descriptor;
            if (! is_array($stored) || $stored === []) {
                continue;
            }

            $distance = face_euclidean_distance($incomingDescriptor, $stored);
            if ($distance <= $threshold && ($bestDistance === null || $distance < $bestDistance)) {
                $bestDistance = $distance;
                $bestUser = $user;
            }
        }

        return ['user' => $bestUser, 'distance' => $bestDistance];
    }
}

if (! function_exists('face_find_matching_staff')) {
    /**
     * @deprecated Use face_find_matching_person()
     *
     * @param  array<int, float>  $incomingDescriptor
     * @return array{user: User|null, distance: float|null}
     */
    function face_find_matching_staff(array $incomingDescriptor, float $threshold = 0.5): array
    {
        return face_find_matching_person($incomingDescriptor, $threshold);
    }
}

if (! function_exists('format_money')) {
    /**
     * Format integer minor/major units for display (JPY whole yen by default).
     */
    function format_money(int $amount, string $currency = 'JPY'): string
    {
        $currency = strtoupper($currency);

        if ($currency === 'JPY') {
            return '¥'.number_format($amount);
        }

        return $currency.' '.number_format($amount / 100, 2);
    }
}
