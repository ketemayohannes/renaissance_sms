<?php

namespace App\Support;

use Andegna\DateTimeFactory;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Ethiopian ⇄ Gregorian conversion for the academic calendar.
 *
 * Conversion is delegated to andegna/calender (Julian-day-number based, handles
 * leap years and Pagume). Never convert by adding a fixed year offset.
 * All dates are calendar dates in Africa/Addis_Ababa; time of day is ignored.
 */
class EthiopianCalendar
{
    public const MONTHS = [
        1 => 'Meskerem', 2 => 'Tikimt', 3 => 'Hidar', 4 => 'Tahsas', 5 => 'Tir', 6 => 'Yekatit',
        7 => 'Megabit', 8 => 'Miyazia', 9 => 'Ginbot', 10 => 'Sene', 11 => 'Hamle', 12 => 'Nehase', 13 => 'Pagume',
    ];

    public const MONTHS_SHORT = [
        1 => 'Mes', 2 => 'Tik', 3 => 'Hid', 4 => 'Tah', 5 => 'Tir', 6 => 'Yek',
        7 => 'Meg', 8 => 'Miy', 9 => 'Gin', 10 => 'Sen', 11 => 'Ham', 12 => 'Neh', 13 => 'Pag',
    ];

    public const MONTHS_AMHARIC = [
        1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ኅዳር', 4 => 'ታኅሣሥ', 5 => 'ጥር', 6 => 'የካቲት',
        7 => 'መጋቢት', 8 => 'ሚያዝያ', 9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ', 13 => 'ጳጉሜን',
    ];

    public static function timezone(): DateTimeZone
    {
        return new DateTimeZone(config('academic_calendar.timezone', 'Africa/Addis_Ababa'));
    }

    /** Today's date in Addis Ababa, at midnight. */
    public static function today(): Carbon
    {
        return Carbon::now(self::timezone())->startOfDay();
    }

    /** Normalise any date input to a midnight Carbon in Addis Ababa, keeping the calendar date. */
    public static function date(CarbonInterface|\DateTimeInterface|string|null $date = null): Carbon
    {
        if ($date === null) {
            return self::today();
        }

        $ymd = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');

        return Carbon::createFromFormat('!Y-m-d', $ymd, self::timezone());
    }

    public static function isLeapYear(int $ethiopianYear): bool
    {
        return $ethiopianYear % 4 === 3;
    }

    public static function daysInMonth(int $ethiopianYear, int $month): int
    {
        self::assertMonth($month);

        return $month === 13 ? (self::isLeapYear($ethiopianYear) ? 6 : 5) : 30;
    }

    /** Ethiopian (year, month, day) → Gregorian date. */
    public static function toGregorian(int $year, int $month, int $day): Carbon
    {
        self::assertMonth($month);
        if ($day < 1 || $day > self::daysInMonth($year, $month)) {
            throw new InvalidArgumentException("Invalid Ethiopian date {$day}/{$month}/{$year}.");
        }

        $gregorian = DateTimeFactory::of($year, $month, $day, 0, 0, 0, self::timezone())->toGregorian();

        return Carbon::createFromFormat('!Y-m-d', $gregorian->format('Y-m-d'), self::timezone());
    }

    /**
     * Gregorian date → ['year' => 2019, 'month' => 1, 'day' => 4].
     *
     * @return array{year:int, month:int, day:int}
     */
    public static function fromGregorian(CarbonInterface|\DateTimeInterface|string $date): array
    {
        $date = self::date($date);
        $eth = DateTimeFactory::fromDateTime(new \DateTime($date->format('Y-m-d').' 12:00:00', self::timezone()));

        return ['year' => $eth->getYear(), 'month' => $eth->getMonth(), 'day' => $eth->getDay()];
    }

    public static function monthName(int $month, bool $short = false, bool $amharic = false): string
    {
        self::assertMonth($month);

        return $amharic ? self::MONTHS_AMHARIC[$month] : ($short ? self::MONTHS_SHORT[$month] : self::MONTHS[$month]);
    }

    /** "Meskerem 4, 2019" / "Mes 4" (short, no year). */
    public static function format(CarbonInterface|\DateTimeInterface|string $date, bool $short = false): string
    {
        $e = self::fromGregorian($date);

        return $short
            ? self::MONTHS_SHORT[$e['month']].' '.$e['day']
            : self::MONTHS[$e['month']].' '.$e['day'].', '.$e['year'];
    }

    /** Human range in Ethiopian dates, e.g. "Tikimt 30 – Hidar 4, 2019". */
    public static function formatRange(CarbonInterface|string $start, CarbonInterface|string $end): string
    {
        $s = self::fromGregorian($start);
        $e = self::fromGregorian($end);

        if ($s == $e) {
            return self::format($start);
        }

        $left = self::MONTHS[$s['month']].' '.$s['day'].($s['year'] !== $e['year'] ? ', '.$s['year'] : '');
        $right = ($s['month'] === $e['month'] && $s['year'] === $e['year'] ? '' : self::MONTHS[$e['month']].' ').$e['day'].', '.$e['year'];

        return $left.' – '.$right;
    }

    /** Academic-year display, e.g. "2019 E.C. / 2026–2027 G.C." */
    public static function academicYearLabel(?int $ethiopianYear, CarbonInterface|string|null $start, CarbonInterface|string|null $end): string
    {
        $greg = ($start && $end) ? self::date($start)->year.'–'.self::date($end)->year.' G.C.' : null;

        return trim(($ethiopianYear ? $ethiopianYear.' E.C.' : '').($ethiopianYear && $greg ? ' / ' : '').($greg ?? ''));
    }

    /** First Gregorian date of an Ethiopian month, and the number of days in it. */
    public static function monthBounds(int $year, int $month): array
    {
        $first = self::toGregorian($year, $month, 1);

        return [$first, $first->copy()->addDays(self::daysInMonth($year, $month) - 1)];
    }

    private static function assertMonth(int $month): void
    {
        if ($month < 1 || $month > 13) {
            throw new InvalidArgumentException("Invalid Ethiopian month {$month}.");
        }
    }
}
