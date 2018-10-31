<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Type;

class TypeCollection
{
    /** @var  TypeInterface[] */
    private $types = [];

    public function __construct(TypeInterface ...$types)
    {
        foreach ($types as $type) {
            $this->addType($type);
        }
    }

    /** @throws  \LogicException when a type name is used twice */
    public function addType(TypeInterface $type) : void
    {
        if ($this->isValidTypeName($type->getName())) {
            throw new \LogicException('Type already defined, cannot add a second time: ' . $type->getName());
        }
        $this->types[$type->getName()] = $type;
    }

    public function isValidTypeName(string $typeName) : bool
    {
        return isset($this->types[$typeName]);
    }

    /** @throws  \OutOfBoundsException when type is not registered */
    public function getTypeFromName(string $typeName) : TypeInterface
    {
        if (!$this->isValidTypeName($typeName)) {
            throw new \OutOfBoundsException('No such type registered: ' . $typeName);
        }
        return $this->types[$typeName];
    }
}
