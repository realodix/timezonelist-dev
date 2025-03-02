<?php

namespace Realodix\Timezone\Exceptions;

class InvalidTimezoneException extends \Exception
{
    public function __construct(string $timezoneId)
    {
        parent::__construct('Invalid timezone: '.$timezoneId);
    }
}
