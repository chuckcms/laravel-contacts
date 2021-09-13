<?php

namespace Chuckcms\Contacts\Exceptions;

use InvalidArgumentException;

class ContactDoesNotExist extends InvalidArgumentException
{
    public static function withId(int $contactId)
    {
        return new static("There is no contact with id `{$contactId}`.");
    }
}
