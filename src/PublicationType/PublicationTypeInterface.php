<?php declare(strict_types = 1);

namespace jschreuder\DocStore\PublicationType;

interface PublicationTypeInterface
{
    /** Returns the string representation for this type */
    public function getName() : string;

    /** Returns a human readable title for this type */
    public function getTitle() : string;
}
