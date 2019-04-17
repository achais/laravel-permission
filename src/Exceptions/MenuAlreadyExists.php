<?php

namespace Achais\Permission\Exceptions;

use InvalidArgumentException;

class MenuAlreadyExists extends InvalidArgumentException
{
    public static function create(string $menuName)
    {
        return new static("A menu `{$menuName}` already.");
    }
}
