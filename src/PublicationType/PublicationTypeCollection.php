<?php declare(strict_types = 1);

namespace jschreuder\DocStore\PublicationType;

class PublicationTypeCollection
{
    /** @var  PublicationTypeInterface[] */
    private $types = [];

    public function __construct(PublicationTypeInterface ...$types)
    {
        foreach ($types as $type) {
            $this->addType($type);
        }
    }

    /** @throws  \DomainException */
    public function addType(PublicationTypeInterface $type) : void
    {
        if ($this->isValidTypeName($type->getName())) {
            throw new \DomainException('Type already defined, cannot add a second time: ' . $type->getName());
        }
        $this->types[$type->getName()] = $type;
    }

    public function isValidTypeName(string $typeName) : bool
    {
        return isset($this->types[$typeName]);
    }

    /** @throws  \OutOfBoundsException */
    public function getTypeFromName(string $typeName) : PublicationTypeInterface
    {
        if (!$this->isValidTypeName($typeName)) {
            throw new \OutOfBoundsException('No such type registered: ' . $typeName);
        }
        return $this->types[$typeName];
    }
}
