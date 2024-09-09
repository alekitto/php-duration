<?php

declare(strict_types=1);

namespace Kcs\Duration\Serializer;

use Kcs\Duration\Duration;
use Kcs\Serializer\Exception\InvalidArgumentException;
use Kcs\Serializer\Handler\DeserializationHandlerInterface;
use Kcs\Serializer\Handler\SerializationHandlerInterface;

use function get_debug_type;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

class DurationHandler implements SerializationHandlerInterface, DeserializationHandlerInterface
{
    public function deserialize(mixed $data): Duration|null
    {
        if ($data instanceof Duration || $data === null) {
            return $data;
        }

        if (is_string($data) || is_float($data)) {
            return new Duration($data);
        }

        if (is_int($data)) {
            return Duration::fromMicroseconds($data);
        }

        throw new InvalidArgumentException('Cannot deserialize duration from given data.');
    }

    public static function getType(): string
    {
        return Duration::class;
    }

    public function serialize(mixed $data): string|null
    {
        if ($data === null) {
            return null;
        }

        if (! $data instanceof Duration) {
            throw new InvalidArgumentException(sprintf('Cannot serialize %s as duration.', get_debug_type($data)));
        }

        return $data->humanize();
    }
}
