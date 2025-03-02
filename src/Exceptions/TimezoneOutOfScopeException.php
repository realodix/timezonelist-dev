<?php

namespace Realodix\Timezone\Exceptions;

class TimezoneOutOfScopeException extends \Exception
{
    public function __construct(string $timezoneId, array $activeGroups)
    {
        asort($activeGroups);
        parent::__construct(sprintf(
            'Timezone "%s" is not within the specified groups: %s.',
            $timezoneId,
            implode(', ', $activeGroups),
        ));
    }
}
