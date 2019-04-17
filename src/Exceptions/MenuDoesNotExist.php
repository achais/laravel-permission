<?php

namespace Achais\Permission\Exceptions;

use InvalidArgumentException;

class MenuDoesNotExist extends InvalidArgumentException
{
    public static function named(string $menuName)
    {
        return new static("There is no menu named `{$menuName}`.");
    }

    public static function withId(int $menuId)
    {
        return new static("There is no menu with id `{$menuId}`.");
    }
}
