<?php

declare(strict_types=1);

namespace Tests\Serializer;

use Kcs\Duration\Duration;
use Kcs\Duration\Serializer\DurationHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DurationHandlerTest extends TestCase
{
    private DurationHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new DurationHandler();
    }

    #[DataProvider('provideHumanizedStringToSeconds')]
    public function testSerialize(int $seconds, string $human): void
    {
        self::assertEquals(
            $human,
            $this->handler->serialize(new Duration($seconds))
        );
    }

    #[DataProvider('provideHumanizedStringToSeconds')]
    public function testDeserialize(int $seconds, string $human): void
    {
        self::assertEquals(
            new Duration($seconds),
            $this->handler->deserialize($human)
        );
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
}
