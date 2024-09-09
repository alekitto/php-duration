<?php

declare(strict_types=1);

namespace Kcs\Duration\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Kcs\Duration\Duration;
use Throwable;

use function class_exists;
use function is_float;
use function is_int;
use function is_string;

class DurationType extends Type
{
    public const NAME = 'duration';

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Duration) {
            return $value->toMicroseconds();
        }

        self::invalidType($value, ['null', Duration::class]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null || $value instanceof Duration) {
            return $value;
        }

        if (! is_int($value) && ! is_float($value) && ! is_string($value)) {
            self::invalidType($value, ['null', 'int', 'string', 'float']);
        }

        try {
            if (is_int($value)) {
                return Duration::fromMicroseconds($value);
            }

            return new Duration($value);
        } catch (Throwable $e) { /** @phpstan-ignore-line */
            self::invalidFormat((string) $value, 'integer', $e);
        }
    }

    /** @param string[] $possibleTypes */
    private static function invalidType(mixed $value, array $possibleTypes, Throwable|null $previous = null): never
    {
        if (class_exists(InvalidType::class)) {
            throw InvalidType::new(
                $value,
                static::class,
                $possibleTypes,
                $previous,
            );
        }

        /** @phpstan-ignore-next-line */
        throw ConversionException::conversionFailedInvalidType(
            $value,
            static::class,
            $possibleTypes,
            $previous,
        );
    }

    private static function invalidFormat(string $value, string $format, Throwable|null $previous = null): never
    {
        if (class_exists(InvalidFormat::class)) {
            throw InvalidFormat::new(
                $value,
                static::class,
                $format,
                $previous,
            );
        }

        /** @phpstan-ignore-next-line */
        throw ConversionException::conversionFailedFormat(
            $value,
            static::class,
            $format,
            $previous,
        );
    }
}
