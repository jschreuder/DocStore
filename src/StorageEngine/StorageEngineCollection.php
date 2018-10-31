<?php declare(strict_types = 1);

namespace jschreuder\DocStore\StorageEngine;

class StorageEngineCollection
{
    /** @var  StorageEngineInterface[] */
    private $storageEngines = [];

    public function __construct(StorageEngineInterface ...$storageEngines)
    {
        foreach ($storageEngines as $storageEngine) {
            $this->addStorageEngine($storageEngine);
        }
    }

    /** @throws  \DomainException */
    public function addStorageEngine(StorageEngineInterface $storageEngine) : void
    {
        if ($this->isValidStorageEngineName($storageEngine->getName())) {
            throw new \DomainException(
                'Storage Engine already defined, cannot add a second time: ' . $storageEngine->getName()
            );
        }
        $this->storageEngines[$storageEngine->getName()] = $storageEngine;
    }

    public function isValidStorageEngineName(string $storageEngineName) : bool
    {
        return isset($this->storageEngines[$storageEngineName]);
    }

    /** @throws  \OutOfBoundsException */
    public function getStorageEngineFromName(string $storageEngineName) : StorageEngineInterface
    {
        if (!$this->isValidStorageEngineName($storageEngineName)) {
            throw new \OutOfBoundsException('No such storage engine registered: ' . $storageEngineName);
        }
        return $this->storageEngines[$storageEngineName];
    }
}
