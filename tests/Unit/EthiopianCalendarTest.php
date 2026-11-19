<?php

namespace Tests\Unit;

use App\Support\EthiopianCalendar;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class EthiopianCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Independent Ethiopian → JDN (Beyene–Kudlek, Amete Mihret era) used to cross-check the library. */
    private function ethiopianToJdn(int $y, int $m, int $d): int
    {
        return 1723856 + 365 + 365 * ($y - 1) + intdiv($y, 4) + 30 * $m + $d - 31;
    }

    /** Independent Gregorian → JDN (Fliegel–Van Flandern). */
    private function gregorianToJdn(int $y, int $m, int $d): int
    {
        $a = intdiv(14 - $m, 12);
        $yy = $y + 4800 - $a;
        $mm = $m + 12 * $a - 3;

        return $d + intdiv(153 * $mm + 2, 5) + 365 * $yy + intdiv($yy, 4) - intdiv($yy, 100) + intdiv($yy, 400) - 32045;
    }

    /** @test */
    public function known_dates_convert_correctly(): void
    {
        $cases = [
            [2019, 1, 1, '2026-09-11'],   // Enkutatash 2019
            [2019, 1, 4, '2026-09-14'],   // school opens (Meskerem 4)
            [2019, 4, 29, '2027-01-07'],  // Genna
            [2019, 5, 11, '2027-01-19'],  // Timket
            [2019, 6, 23, '2027-03-02'],  // Adwa
            [2019, 10, 27, '2027-07-04'], // Parent's day (a Sunday)
            [2019, 13, 6, '2027-09-11'],  // Pagume 6 — 2019 is a leap year
            [2020, 1, 1, '2027-09-12'],   // the year after a leap year starts on Sept 12
            [2016, 1, 1, '2023-09-12'],
            [2017, 1, 1, '2024-09-11'],
        ];

        foreach ($cases as [$y, $m, $d, $greg]) {
            $this->assertSame($greg, EthiopianCalendar::toGregorian($y, $m, $d)->toDateString(), "{$d}/{$m}/{$y}");
            $this->assertSame(['year' => $y, 'month' => $m, 'day' => $d], EthiopianCalendar::fromGregorian($greg), $greg);
        }
    }

    /** @test */
    public function conversion_matches_an_independent_algorithm_and_round_trips_for_every_day(): void
    {
        for ($y = 2012; $y <= 2024; $y++) {
            for ($m = 1; $m <= 13; $m++) {
                for ($d = 1; $d <= EthiopianCalendar::daysInMonth($y, $m); $d++) {
                    $g = EthiopianCalendar::toGregorian($y, $m, $d);
                    $this->assertSame(
                        $this->ethiopianToJdn($y, $m, $d),
                        $this->gregorianToJdn($g->year, $g->month, $g->day),
                        "JDN mismatch for {$d}/{$m}/{$y}"
                    );
                    $this->assertSame(['year' => $y, 'month' => $m, 'day' => $d], EthiopianCalendar::fromGregorian($g));
                }
            }
        }
    }

    /** @test */
    public function leap_years_and_pagume_are_handled(): void
    {
        $this->assertTrue(EthiopianCalendar::isLeapYear(2019));
        $this->assertFalse(EthiopianCalendar::isLeapYear(2020));
        $this->assertTrue(EthiopianCalendar::isLeapYear(2023));
        $this->assertSame(6, EthiopianCalendar::daysInMonth(2019, 13));
        $this->assertSame(5, EthiopianCalendar::daysInMonth(2020, 13));
        $this->assertSame(30, EthiopianCalendar::daysInMonth(2020, 5));

        $this->expectException(InvalidArgumentException::class);
        EthiopianCalendar::toGregorian(2020, 13, 6);
    }

    /** @test */
    public function invalid_months_and_days_are_rejected(): void
    {
        foreach ([[2019, 0, 1], [2019, 14, 1], [2019, 1, 31], [2019, 1, 0]] as [$y, $m, $d]) {
            try {
                EthiopianCalendar::toGregorian($y, $m, $d);
                $this->fail("Expected {$d}/{$m}/{$y} to be rejected");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /** @test */
    public function today_uses_addis_ababa_time(): void
    {
        // 22:30 UTC on Sept 10 is already 01:30 on Sept 11 (Meskerem 1, 2019) in Addis Ababa.
        Carbon::setTestNow(Carbon::parse('2026-09-10 22:30:00', 'UTC'));

        $this->assertSame('2026-09-11', EthiopianCalendar::today()->toDateString());
        $this->assertSame(['year' => 2019, 'month' => 1, 'day' => 1], EthiopianCalendar::fromGregorian(EthiopianCalendar::today()));
    }

    /** @test */
    public function ranges_and_labels_are_formatted_across_months(): void
    {
        $this->assertSame('Tikimt 30 – Hidar 4, 2019', EthiopianCalendar::formatRange('2026-11-09', '2026-11-13'));
        $this->assertSame('Meskerem 4 – 8, 2019', EthiopianCalendar::formatRange('2026-09-14', '2026-09-18'));
        $this->assertSame('Pagume 1, 2019 – Meskerem 2, 2020', EthiopianCalendar::formatRange('2027-09-06', '2027-09-13'));
        $this->assertSame('2019 E.C. / 2026–2027 G.C.', EthiopianCalendar::academicYearLabel(2019, '2026-09-14', '2027-07-04'));

        [$first, $last] = EthiopianCalendar::monthBounds(2019, 13);
        $this->assertSame(['2027-09-06', '2027-09-11'], [$first->toDateString(), $last->toDateString()]);
    }
}
