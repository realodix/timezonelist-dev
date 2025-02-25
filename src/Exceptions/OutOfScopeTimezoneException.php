<?php

namespace Realodix\Timezonelist\Exceptions;

class OutOfScopeTimezoneException extends \Exception
{
    /**
     * Creates a new \Exception instance.
     */
    public function __construct(string $timezone, array $groupsFilter)
    {
        sort($groupsFilter);

        parent::__construct(
            sprintf(
                'Timezone %s is not within the specified groups: %s',
                $timezone,
                implode(', ', $groupsFilter),
            ),
        );
    }
}
