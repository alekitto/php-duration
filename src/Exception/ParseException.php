<?php

declare(strict_types=1);

namespace Kcs\Duration\Exception;

use InvalidArgumentException;

use function sprintf;
use function var_export;

class ParseException extends InvalidArgumentException
{
    public function __construct(public readonly string $invalidDuration)
    {
        parent::__construct(sprintf('Cannot parse %s as duration', var_export($this->invalidDuration, true)));
    }
}
