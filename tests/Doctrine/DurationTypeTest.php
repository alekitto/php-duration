<?php

declare(strict_types=1);

namespace Tests\Doctrine;

use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Kcs\Duration\Doctrine\DurationType;
use Kcs\Duration\Duration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class DurationTypeTest extends TestCase
{
    private AbstractPlatform&MockObject $platform;
    private DurationType $type;

    protected function setUp(): void
    {
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->type = new DurationType();
    }

    public function testConvertsToDatabaseValue(): void
    {
        $interval = new Duration('4h 3m 12.45878s');

        $expected = (4 * 3600 + 3 * 60 + 12) * 1_000_000 + 458780;
        $actual   = $this->type->convertToDatabaseValue($interval, $this->platform);

        self::assertEquals($expected, $actual);
    }

    public function testConvertsToPHPValue(): void
    {
        $interval = $this->type->convertToPHPValue(56478235648, $this->platform);

        self::assertInstanceOf(Duration::class, $interval);
        self::assertEquals('15h 41m 18.235648s', $interval->humanize());
    }

    #[DataProvider('invalidPHPValuesProvider')]
    public function testInvalidTypeConversionToDatabaseValue(mixed $value): void
    {
        $this->expectException(ConversionException::class);

        $this->type->convertToDatabaseValue($value, $this->platform);
    }

    /** @return mixed[][] */
    public static function invalidPHPValuesProvider(): iterable
    {
        return [
            [0],
            [''],
            ['foo'],
            ['testtest'],
            [new stdClass()],
            [27],
            [-1],
            [1.2],
            [[]],
            [['an array']],
            [new DateTime()],
        ];
    }
}
