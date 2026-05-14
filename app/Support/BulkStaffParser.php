<?php

namespace App\Support;

final class BulkStaffParser
{
    /**
     * One staff per line: "Full Name" or "Full Name, email@example.com".
     *
     * @return list<array{line: int, name: string, email: ?string}>
     */
    public static function parse(string $list): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $list) ?: [];
        $rows = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $lineNo = $i + 1;

            if (str_contains($line, ',')) {
                [$name, $email] = array_map('trim', explode(',', $line, 2));
            } else {
                $name = $line;
                $email = '';
            }

            $rows[] = [
                'line' => $lineNo,
                'name' => $name,
                'email' => $email === '' ? null : mb_strtolower($email),
            ];
        }

        return $rows;
    }
}
