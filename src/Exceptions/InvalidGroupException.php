<?php

namespace Realodix\Timezone\Exceptions;

class InvalidGroupException extends \Exception
{
    public function __construct(array $invalidGroups)
    {
        parent::__construct('Invalid groups: '.implode(', ', $invalidGroups));
    }
}
