<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffAttendanceWorkbook
{
    /**
     * @param  Collection<int, User>  $people
     * @param  Collection<string, Attendance>  $records  keyed by "{userId}|Y-m-d"
     */
    public static function make(Collection $people, Collection $records, Carbon $start): Spreadsheet
    {
        $end = $start->copy()->endOfMonth();
        $book = new Spreadsheet;
        $book->getProperties()
            ->setTitle('Staff attendance '.$start->format('F Y'))
            ->setCreator('Attendance');

        self::summarySheet($book->getActiveSheet(), $people, $records, $start, $end);

        $usedTitles = ['Summary' => true];
        foreach ($people as $person) {
            $sheet = $book->createSheet();
            $sheet->setTitle(self::sheetTitle($person, $usedTitles));
            self::personSheet($sheet, $person, $records, $start, $end);
        }

        $book->setActiveSheetIndex(0);

        return $book;
    }

    /**
     * @param  Collection<int, User>  $people
     * @param  Collection<string, Attendance>  $records
     */
    private static function summarySheet(Worksheet $sheet, Collection $people, Collection $records, Carbon $start, Carbon $end): void
    {
        $sheet->setTitle('Summary');
        $sheet->setCellValue('A1', 'Staff attendance');
        $sheet->setCellValue('B1', $start->format('F Y'));
        $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(14);

        $headers = ['Name', 'Role', 'Weekdays', 'Days with record', 'Complete', 'Incomplete', 'No record', 'Total hours'];
        $sheet->fromArray($headers, null, 'A3');
        self::headerStyle($sheet, 'A3:H3');

        $row = 4;
        foreach ($people as $person) {
            $stats = self::stats($person, $records, $start, $end);
            $sheet->fromArray([
                $person->name,
                $person->roleLabel(),
                $stats['weekdays'],
                $stats['with_record'],
                $stats['complete'],
                $stats['incomplete'],
                $stats['no_record'],
                self::formatDuration($stats['seconds']),
            ], null, 'A'.$row);
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->freezePane('A4');
    }

    /**
     * @param  Collection<string, Attendance>  $records
     */
    private static function personSheet(Worksheet $sheet, User $person, Collection $records, Carbon $start, Carbon $end): void
    {
        $stats = self::stats($person, $records, $start, $end);

        $sheet->setCellValue('A1', $person->name);
        $sheet->setCellValue('B1', $person->roleLabel());
        $sheet->setCellValue('C1', $start->format('F Y'));
        $sheet->setCellValue('D1', 'Total '.$stats['complete'].' complete · '.self::formatDuration($stats['seconds']));
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $sheet->fromArray(['Date', 'Weekday', 'Check-in', 'Check-out', 'Duration', 'Manual checkout'], null, 'A3');
        self::headerStyle($sheet, 'A3:F3');

        $row = 4;
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $record = $records->get($person->id.'|'.$day->toDateString());
            $seconds = self::workedSeconds($record);
            $sheet->fromArray([
                $day->toDateString(),
                $day->format('l'),
                self::clock($record?->checkin_time),
                self::clock($record?->checkout_time),
                $seconds !== null ? self::formatDuration($seconds) : '',
                $record?->manual_checkout ? 'Yes' : '',
            ], null, 'A'.$row);

            if (! $day->isWeekday()) {
                $sheet->getStyle('A'.$row.':F'.$row)->getFont()->getColor()->setRGB('94A3B8');
            }
            $row++;
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->freezePane('A4');
    }

    /**
     * @param  Collection<string, Attendance>  $records
     * @return array{weekdays: int, with_record: int, complete: int, incomplete: int, no_record: int, seconds: int}
     */
    private static function stats(User $person, Collection $records, Carbon $start, Carbon $end): array
    {
        $stats = [
            'weekdays' => 0,
            'with_record' => 0,
            'complete' => 0,
            'incomplete' => 0,
            'no_record' => 0,
            'seconds' => 0,
        ];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $record = $records->get($person->id.'|'.$day->toDateString());
            $seconds = self::workedSeconds($record);
            if ($seconds !== null) {
                $stats['seconds'] += $seconds;
            }

            if (! $day->isWeekday()) {
                continue;
            }

            $stats['weekdays']++;
            if ($record && ($record->checkin_time || $record->checkout_time)) {
                $stats['with_record']++;
                if ($record->checkin_time && $record->checkout_time) {
                    $stats['complete']++;
                } else {
                    $stats['incomplete']++;
                }
            } else {
                $stats['no_record']++;
            }
        }

        return $stats;
    }

    private static function workedSeconds(?Attendance $record): ?int
    {
        if (! $record?->checkin_time || ! $record->checkout_time) {
            return null;
        }

        if ($record->checkout_time->lte($record->checkin_time)) {
            return null;
        }

        return (int) $record->checkin_time->diffInSeconds($record->checkout_time);
    }

    private static function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0h';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($minutes === 0) {
            return $hours.'h';
        }

        return $hours.'h '.$minutes.'m';
    }

    private static function clock(mixed $time): string
    {
        if ($time === null) {
            return '';
        }

        return Carbon::parse($time)->format('H:i');
    }

    private static function headerStyle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');
    }

    /**
     * @param  array<string, true>  $used
     */
    private static function sheetTitle(User $person, array &$used): string
    {
        $base = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/u', '', $person->name) ?: 'Person';
        $base = trim((string) $base) ?: 'Person';
        $base = mb_substr($base, 0, 28);

        $title = $base;
        $n = 2;
        while (isset($used[$title])) {
            $suffix = ' '.$n;
            $title = mb_substr($base, 0, 31 - mb_strlen($suffix)).$suffix;
            $n++;
        }

        $used[$title] = true;

        return $title;
    }
}
