<?php

declare(strict_types=1);

namespace Tests;

use Kcs\Duration\Duration;
use Kcs\Duration\Exception\ParseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DurationTest extends TestCase
{
    public static function secondsSampleData(): array
    {
        return [
            [0, null],
            [new ParseException(' '), ' '],
            [0, '0 s'],
            [1, '1 s'],
            [1, '1 sec'],
            [3, '3S'],
            [7, '7 S'],
            [51, '51seconds'],
            [4, '4 Sec.'],
            [15, '15 SEcONDs'],
            [1, '1.0 s'],
        ];
    }

    #[DataProvider('secondsSampleData')]
    public function testGettingValueFromSecondSuffixes(int|ParseException $expectedSeconds, string|null $secStr): void
    {
        if ($expectedSeconds instanceof ParseException) {
            $this->expectException(ParseException::class);
            $this->expectExceptionMessage($expectedSeconds->getMessage());
        }

        $d = new Duration($secStr);
        $this->assertEquals($expectedSeconds, $d->seconds);
    }

    public static function microsecondsSampleData(): array
    {
        return [
            [1, 568_900, '1.5689 S'],
            [1, 3420, '1.00342 S'],
        ];
    }

    #[DataProvider('microsecondsSampleData')]
    public function testGettingValueFromMicrosecondSuffixes(int $expectedSeconds, int $expectedMicroseconds, string $secStr): void
    {
        $d = new Duration($secStr);
        $this->assertEquals($expectedSeconds, $d->seconds);
        $this->assertEquals($expectedMicroseconds, $d->microseconds);
    }

    public static function minutesSampleData(): array
    {
        return [
            [0, '0m'],
            [1, '1 m'],
            [4, '4 min'],
            [6, '6M'],
            [14, '14 Ms'],
            [31, '31 minutes'],
            [9, '9Min.'],
            [11, '11 MINUTE'],
        ];
    }

    #[DataProvider('minutesSampleData')]
    public function testGettingValueFromMinuteSuffixes($intVal, $minStr): void
    {
        $d = new Duration($minStr);
        $this->assertEquals($intVal, $d->minutes);
    }

    public static function hoursSampleData(): array
    {
        return [
            [0, '0h'],
            [1, '1 h'],
            [1, '1 hr'],
            [1, '1H'],
            [3, '3hours'],
            [6, '6HoUr'],
            [14, '14 HOURs'],
            [12, '36h'],
        ];
    }

    #[DataProvider('hoursSampleData')]
    public function testGettingValueFromHourSuffixes($intVal, $hrStr): void
    {
        $d = new Duration($hrStr);
        $this->assertEquals($intVal, $d->hours);
    }

    public static function daysSampleData(): array
    {
        return [
            [0, '0d'],
            [1, '1 d'],
            [1, '1 D'],
            [1, '1D'],
            [1, '24 Hrs'],
            [24, '24 ds'],
            [3, '3days'],
            [6, '6DaY'],
            [14, '14 DAYs'],
        ];
    }

    #[DataProvider('daysSampleData')]
    public function testGettingValueFromDaySuffixes($intVal, $dayStr): void
    {
        $d = new Duration($dayStr);
        $this->assertEquals($intVal, $d->days);
    }

    #[DataProvider('provideSecondsAsFormattedString')]
    public function testConvertingSecondsToFormattedString(string $expected, int|float $duration): void
    {
        $this->assertEquals($expected, (new Duration($duration))->formatted());
    }

    public static function provideSecondsAsFormattedString(): array
    {
        return [
            ['0', 0],
            ['4', 4],
            ['9', 9],
            ['42', 42],
            ['1:02', 62],
            ['1:09', 69],
            ['1:42', 102],
            ['10:47', 647],
            ['1:00:00', 3600],
            ['1:00:01', 3601],
            ['1:00:11', 3611],
            ['1:01:00', 3660],
            ['1:01:14', 3674],
            ['1:04:25', 3865],
            ['1:09:09', 4149],

            // microseconds
            ['0', 0.0],
            ['4.987', 4.987],
            ['9.123', 9.123],
            ['42.672', 42.672],
            ['1:02.23', 62.23],
            ['1:09.9', 69.9],
            ['1:42.62394', 102.62394],
            ['10:47.5', 647.5],
            ['1:00:00.954', 3600.954],
            ['1:00:01.5123', 3601.5123],
            ['1:00:11.041237', 3611.0412368456],
            ['1:01:00.56945', 3660.56945],
            ['1:01:14.3', 3674.3],
            ['1:04:25.00056', 3865.0005598],
            ['1:09:09.123', 4149.123],
        ];
    }

    #[DataProvider('provideSecondsToFormattedStringZeroFilled')]
    public function testConvertingSecondsToFormattedStringZeroFilled(string $formatted, int|float $seconds): void
    {
        $this->assertEquals($formatted, (new Duration($seconds))->formatted(true));
    }

    public static function provideSecondsToFormattedStringZeroFilled(): array
    {
        return [
            ['0:00:00', 0],
            ['0:00:04', 4],
            ['0:00:09', 9],
            ['0:00:42', 42],
            ['0:01:02', 62],
            ['0:01:09', 69],
            ['0:01:42', 102],
            ['0:10:47', 647],
            ['1:00:00', 3600],
            ['1:00:01', 3601],
            ['1:00:11', 3611],
            ['1:01:00', 3660],
            ['1:01:14', 3674],
            ['1:04:25', 3865],
            ['1:09:09', 4149],

            // microseconds
            ['0:00:04.542', 4.542],
            ['1:09:09.0987', 4149.0987],
        ];
    }

    #[DataProvider('provideFormattedStringsToSeconds')]
    public function testConvertingFormattedStringsToSeconds(int|float $expected, string $duration, int|bool $rounding = false): void
    {
        $this->assertEquals($expected, (new Duration($duration))->toSeconds($rounding));
    }

    public static function provideFormattedStringsToSeconds(): array
    {
        return [
            [0, '0', ],
            [4, '4', ],
            [9, '9', ],
            [42, '42', ],
            [62, '1:02', ],
            [69, '1:09', ],
            [102, '1:42', ],
            [647, '10:47', ],
            [3600, '1:00:00', ],
            [3601, '1:00:01', ],
            [3611, '1:00:11', ],
            [3660, '1:01:00', ],
            [3674, '1:01:14', ],
            [3865, '1:04:25', ],
            [4149, '1:09:09', ],

            // microseconds
            [4.6, '4.6', ],
            [9.5, '9.5', ],
            [42.1, '42.1', ],
            [62.96, '1:02.96', ],
            [69.23, '1:09.23', ],
            [102.55, '1:42.55', ],
            [647.999, '10:47.999', ],
            [3600.9987, '1:00:00.9987', ],
            [3601.000111, '1:00:01.000111', ],
            [3611.0999, '1:00:11.0999', ],
            [3660.500001, '1:01:00.500001', ],
            [3674.00001, '1:01:14.00001', ],
            [3865.499999, '1:04:25.499999', ],
            [4149.499999, '1:09:09.499999', ],

            // precision
            [0, '0', 0],
            [5, '4.6', 0],
            [10, '9.5', 0],
            [42.1, '42.1', 1],
            [63, '1:02.96', 1],
            [69.23, '1:09.23', ],
            [102.55, '1:42.55', 2],
            [648, '10:47.999', 2],
            [3601, '1:00:00.9987', 2],
            [3601, '1:00:01.000111', 3],
            [3611.0999, '1:00:11.0999', 4],
            [3660.5, '1:01:00.500001', 2],
            [3674, '1:01:14.00001', 2],
            [3865.5, '1:04:25.499999', 3],
            [4149.499997, '1:09:09.4999971', 6],
        ];
    }

    #[DataProvider('provideFormattedStringsToMinutes')]
    public function testConvertingFormattedStringsToMinutes(int|float $expected, string $duration, int|bool $rounding = false): void
    {
        $this->assertEquals($expected, (new Duration($duration))->toMinutes($rounding));
    }

    public static function provideFormattedStringsToMinutes(): array
    {
        return [
            [0, '0', ],
            [4 / 60, '4', ],
            [9 / 60, '9', ],
            [42 / 60, '42', ],
            [62 / 60, '1:02', ],
            [69 / 60, '1:09', ],
            [102 / 60, '1:42', ],
            [647 / 60, '10:47', ],
            [3600 / 60, '1:00:00', ],
            [3601 / 60, '1:00:01', ],
            [3611 / 60, '1:00:11', ],
            [3660 / 60, '1:01:00', ],
            [3674 / 60, '1:01:14', ],
            [3865 / 60, '1:04:25', ],
            [4149 / 60, '1:09:09', ],

            // to integer
            [0, '0', true],
            [0, '4', true],
            [0, '9', true],
            [1, '42', true],
            [1, '1:02', true],
            [1, '1:09', true],
            [2, '1:42', true],
            [11, '10:47', true],
            [60, '1:00:00', true],
            [60, '1:00:01', true],
            [60, '1:00:11', true],
            [61, '1:01:00', true],
            [61, '1:01:14', true],
            [64, '1:04:25', true],
            [65, '1:04:55', true],
            [69, '1:09:09', true],

            // precision
            [0, '0', 0],
            [0, '4', 0],
            [0, '9', 0],
            [1, '42', 0],
            [1, '1:02', 0],
            [1, '1:09', 0],
            [2, '1:42', 0],
            [11, '10:47', 0],
            [60, '1:00:00', 0],
            [60, '1:00:01', 0],
            [60, '1:00:11', 0],
            [61, '1:01:00', 0],
            [61, '1:01:14', 0],
            [64, '1:04:25', 0],
            [65, '1:04:55', 0],
            [69, '1:09:09', 0],

            [0, '0', 1],
            [0.1, '4', 1],
            [0.15, '9', 2],
            [0.7, '42', 3],
            [1, '1:02', 1],
            [1.15, '1:09', 2],
            [1.7, '1:42', 3],
            [10.8, '10:47', 1],
            [60, '1:00:00', 2],
            [60.017, '1:00:01', 3],
            [60.2, '1:00:11', 1],
            [61, '1:01:00', 2],
            [61.233, '1:01:14', 3],
            [64.42, '1:04:25', 2],
            [64.92, '1:04:55', 2],
            [69.15, '1:09:09', 2],
        ];
    }

    #[DataProvider('provideSecondsToHumanizedString')]
    public function testConvertSecondsToHumanizedString(string $expected, int|float $duration): void
    {
        $this->assertEquals($expected, (new Duration($duration))->humanize());
    }

    public static function provideSecondsToHumanizedString(): array
    {
        return [
            ['0s', 0],
            ['4s', 4],
            ['42s', 42],
            ['1m 2s', 62],
            ['1m 42s', 102],
            ['10m 47s', 647],
            ['1h', 3600],
            ['1h 5s', 3605],
            ['1h 1m', 3660],
            ['1h 1m 5s', 3665],
            ['3d', 259200],
            ['2d 11h 30m', 214200],
    
            ['4.0596s', 4.0596],
            ['2d 11h 30m 0.9542s', 214200.9542],
        ];
    }

    #[DataProvider('provideHumanizedStringToSeconds')]
    public function testConvertHumanizedStringToSeconds(int $expected, string $duration): void
    {
        $this->assertEquals($expected, (new Duration($duration))->toSeconds());
    }
    
    public static function provideHumanizedStringToSeconds(): array
    {
        return [
            [0, '0s'],
            [4, '4s'],
            [42, '42s'],
            [72, '1m 12s'],
            [102, '1m 42s'],
            [647, '10m 47s'],
            [3600, '1h'],
            [3605, '1h 5s'],
            [3660, '1h 1m'],
            [3665, '1h 1m 5s'],
            [86400, '1d'],
            [214200, '2d 11h 30m'],
            [214214, '2d 11h 30m 14s'],
        ];
    }

    public function testConvertHumanizedStringToSeconds7HourDay(): void
    {
        $this->assertEquals(0, (new Duration('0d', 7))->toSeconds());
        $this->assertEquals(25200, (new Duration('1d', 7))->toSeconds());
        $this->assertEquals(91800, (new Duration('2d 11h 30m', 7))->toSeconds());
    }

    public function testSupportDecimals(): void
    {
        $this->assertEquals(0, (new Duration('0d', 6))->toMinutes());
        $this->assertEquals(6 * 60, (new Duration('1d', 6))->toMinutes());
        $this->assertEquals((6 + 3) * 60, (new Duration('1.5d', 6))->toMinutes());
        $this->assertEquals(60, (new Duration('1h', 6))->toMinutes());
        $this->assertEquals(60 + 30, (new Duration('1.5h', 6))->toMinutes());
        $this->assertEquals((12 * 60) + 60 + 30, (new Duration('2d 1.5h', 6))->toMinutes());
    }

    public function testConvertHumanizedWithSupportDecimals(): void
    {
        $t = '1.5d 1.5h 2m 5s';

        $this->assertEquals('1d 4h 32m 5s', (new Duration($t, 6))->humanize(), "Test humanize with: $t");
        $this->assertEquals('10:32:05', (new Duration($t, 6))->formatted(), "Test formatted with: $t");
        $this->assertEquals(37925, (new Duration($t, 6))->toSeconds(), "Test toSeconds with: $t");
        $this->assertEquals(37925/60, (new Duration($t, 6))->toMinutes(), "Test toMinutes with: $t");
        $this->assertEquals(632, (new Duration($t, 6))->toMinutes(0), "Test toMinutes with: $t");
    }
}
