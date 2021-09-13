<?php

namespace Chuckcms\Contacts\Contracts;

interface Contact
{
    /**
     * Find a contact by its id.
     *
     * @param int $id
     *
     * @throws \Chuckcms\Contacts\Exceptions\ContactDoesNotExist
     *
     * @return \Chuckcms\Contacts\Contracts\Contact
     */
    public static function findById(int $id): self;
}
