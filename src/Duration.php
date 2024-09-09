<?php

declare(strict_types=1);

namespace Kcs\Duration;

use Kcs\Duration\Exception\ParseException;

use function count;
use function explode;
use function floor;
use function is_numeric;
use function preg_match;
use function round;
use function rtrim;
use function sprintf;
use function str_contains;
use function strlen;
use function strpos;
use function substr;
use function trim;

final readonly class Duration
{
    private const DAYS_REGEX = '/([0-9.]+)\s?[dD]/';
    private const HOURS_REGEX = '/([0-9.]+)\s?[hH]/';
    private const MINUTES_REGEX = '/(\d+)\s?[mM]/';
    private const SECONDS_REGEX = '/(\d+(\.\d+)?)\s?[sS]/';

    public int $days;
    public int $hours;
    public int $minutes;
    public int $seconds;
    public int $microseconds;

    public function __construct(int|float|string|null $duration = null, private int $hoursPerDay = 24)
    {
        if ($duration === null) {
            $this->microseconds = $this->seconds = $this->minutes = $this->hours = $this->days = 0;

            return;
        }

        $seconds = 0;
        $minutes = 0;
        $hours = 0;
        $days = 0;

        if (is_numeric($duration)) {
            $seconds = (float) $duration;
        } elseif (str_contains($duration, ':')) {
            $parts = explode(':', $duration);

            if (count($parts) === 2) {
                $minutes = (int) $parts[0];
                $seconds = (float) $parts[1];
            } elseif (count($parts) === 3) {
                $hours = (int) $parts[0];
                $minutes = (int) $parts[1];
                $seconds = (float) $parts[2];
            }
        } elseif (
            preg_match(self::DAYS_REGEX, $duration) ||
            preg_match(self::HOURS_REGEX, $duration) ||
            preg_match(self::MINUTES_REGEX, $duration) ||
            preg_match(self::SECONDS_REGEX, $duration)
        ) {
            if (preg_match(self::DAYS_REGEX, $duration, $matches)) {
                $num = $this->numberBreakdown((float) $matches[1]);
                $days += (int) $num[0];
                $hours += $num[1] * $this->hoursPerDay;
            }

            if (preg_match(self::HOURS_REGEX, $duration, $matches)) {
                $num = $this->numberBreakdown((float) $matches[1]);
                $hours += (int) $num[0];
                $minutes += $num[1] * 60;
            }

            if (preg_match(self::MINUTES_REGEX, $duration, $matches)) {
                $minutes += (int) $matches[1];
            }

            if (preg_match(self::SECONDS_REGEX, $duration, $matches)) {
                $seconds += (float) $matches[1];
            }
        } else {
            throw new ParseException($duration);
        }

        if ($seconds >= 60) {
            $minutes = (int) floor($seconds / 60);

            // count current precision
            $precision = 0;
            $delimiterPos = strpos((string) $seconds, '.');
            if ($delimiterPos !== false) {
                $precision = strlen(substr((string) $seconds, $delimiterPos + 1));
            }

            $seconds = round($seconds - ($minutes * 60), $precision);
        }

        $this->seconds = (int) $seconds;
        $this->microseconds = (int) round(($seconds - $this->seconds) * 1_000_000);

        if ($minutes >= 60) {
            $hours = (int) floor($minutes / 60);
            $this->minutes = (int) ($minutes - ($hours * 60));
        } else {
            $this->minutes = (int) $minutes;
        }

        if ($hours >= $this->hoursPerDay) {
            $d = (int) floor($hours / $this->hoursPerDay);
            $this->hours = (int) ($hours - ($d * $this->hoursPerDay));
            $this->days = $days + $d;
        } else {
            $this->days = $days;
            $this->hours = (int) $hours;
        }
    }

    public static function fromMicroseconds(int $value): self
    {
        return new self(sprintf('%u.%06u', $value / 1_000_000, $value % 1_000_000));
    }

    /**
     * Returns the duration as an amount of seconds.
     *
     * For example, one hour and 42 minutes would be "6120"
     *
     * @param int|bool $precision Number of decimal digits to round to. If set to false, the number is not rounded.
     */
    public function toSeconds(int|bool $precision = false): int|float
    {
        $output = $this->toMicroseconds() / 1_000_000.0;

        return match ($precision) {
            false => $output,
            true, 0 => (int) round($output),
            default => round($output, $precision),
        };
    }

    /**
     * Returns the duration as an amount of milliseconds.
     *
     * For example, one hour and 42 minutes would be "6_120_000"
     */
    public function toMilliseconds(): int
    {
        return (int) round($this->toMicroseconds() / 1000);
    }

    /**
     * Returns the duration as an amount of microseconds.
     *
     * For example, one hour and 42 minutes would be "6_120_000_000"
     */
    public function toMicroseconds(): int
    {
        return (
                ($this->days * $this->hoursPerDay * 60 * 60) +
                ($this->hours * 60 * 60) +
                ($this->minutes * 60) +
                $this->seconds
            ) * 1_000_000 +
            $this->microseconds;
    }

    /**
     * Returns the duration as an amount of minutes.
     *
     * For example, one hour and 42 minutes would be "102" minutes
     *
     * @param int|bool $precision Number of decimal digits to round to. If set to false, the number is not rounded.
     */
    public function toMinutes(int|bool $precision = false): int|float
    {
        $result = $this->toSeconds() / 60.0;

        return match ($precision) {
            false => $result,
            true, 0 => (int) round($result),
            default => round($result, $precision),
        };
    }

    /**
     * Returns the duration as a colon formatted string
     *
     * For example, one hour and 42 minutes would be "1:43"
     * With $zeroFill to true :
     *   - 42 minutes would be "0:42:00"
     *   - 28 seconds would be "0:00:28"
     *
     * @param bool $zeroFill A boolean, to force zero-fill result or not (see example)
     */
    public function formatted(bool $zeroFill = false): string
    {
        $hours = $this->hours + ($this->days * $this->hoursPerDay);
        $output = '';

        if ($this->seconds > 0 || $this->microseconds > 0) {
            if ($this->seconds < 10 && ($this->minutes > 0 || $hours > 0 || $zeroFill)) {
                $output .= '0' . $this->seconds;
            } else {
                $output .= $this->seconds;
            }

            if ($this->microseconds !== 0) {
                $output .= rtrim(sprintf('.%06u', $this->microseconds), '0');
            }
        } elseif ($this->minutes > 0 || $hours > 0 || $zeroFill) {
            $output = '00';
        } else {
            $output = '0';
        }

        if ($this->minutes > 0) {
            if ($this->minutes <= 9 && ($hours > 0 || $zeroFill)) {
                $output = '0' . $this->minutes . ':' . $output;
            } else {
                $output = $this->minutes . ':' . $output;
            }
        } elseif ($hours > 0 || $zeroFill) {
            $output = '00:' . $output;
        }

        if ($hours > 0) {
            $output = $hours . ':' . $output;
        } elseif ($zeroFill) {
            $output = '0:' . $output;
        }

        return trim($output);
    }

    /**
     * Returns the duration as a human-readable string.
     *
     * For example, one hour and 42 minutes would be "1h 42m"
     */
    public function humanize(): string
    {
        $output = '';
        if ($this->seconds > 0 || $this->microseconds > 0 || ($this->seconds === 0 && $this->minutes === 0 && $this->hours === 0 && $this->days === 0)) {
            $output .= $this->seconds;
            if ($this->microseconds !== 0) {
                $output .= rtrim(sprintf('.%06u', $this->microseconds), '0');
            }

            $output .= 's';
        }

        if ($this->minutes > 0) {
            $output = $this->minutes . 'm ' . $output;
        }

        if ($this->hours > 0) {
            $output = $this->hours . 'h ' . $output;
        }

        if ($this->days > 0) {
            $output = $this->days . 'd ' . $output;
        }

        return trim($output);
    }

    /** @return array|float[]|int[] */
    private function numberBreakdown(float $number): array
    {
        $negative = 1;
        if ($number < 0) {
            $negative = -1;
            $number *= -1;
        }

        return [
            floor($number) * $negative,
            ($number - floor($number)) * $negative,
        ];
    }
}
