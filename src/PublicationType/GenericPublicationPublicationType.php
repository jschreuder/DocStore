<?php declare(strict_types = 1);

namespace jschreuder\DocStore\PublicationType;

class GenericPublicationPublicationType implements PublicationTypeInterface
{
    /** @var  string */
    private $name;

    /** @var  string */
    private $title;

    public function __construct(string $name, string $title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
}
