<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Entity;

use Ramsey\Uuid\UuidInterface;

class Document
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Publication */
    private $publication;

    /** @var  string */
    private $type;

    /** @var  string */
    private $storageEngine;

    /** @var  string */
    private $title;

    /** @var  string */
    private $fileName;

    /** @var  integer */
    private $fileSize;

    /** @var  string */
    private $mimeType;

    /** @var  \DateTimeInterface */
    private $created;

    /** @var  ?\DateTimeInterface */
    private $updated;

    /** @var  ?\DateTimeInterface */
    private $removed;

    /** @throws  \DomainException */
    public function __construct(
        UuidInterface $id,
        Publication $publication,
        string $type,
        string $storageEngine,
        string $title,
        int $fileName,
        int $fileSize,
        int $mimeType,
        \DateTimeInterface $created,
        ?\DateTimeInterface $updated,
        ?\DateTimeInterface $removed
    ) {
        if ($publication->isPublished()) {
            throw new \DomainException('Cannot create document for already published Publication');
        }

        $this->id = $id;
        $this->publication = $publication;
        $this->type = $type;
        $this->storageEngine = $storageEngine;
        $this->title = $title;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
        $this->mimeType = $mimeType;
        $this->created = $created;
        $this->updated = $updated;
        $this->removed = $removed;
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getPublication() : Publication
    {
        return $this->publication;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getStorageEngine() : string
    {
        return $this->storageEngine;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getFileName() : string
    {
        return $this->fileName;
    }

    public function getFileSize() : int
    {
        return $this->fileSize;
    }

    public function getMimeType() : string
    {
        return $this->mimeType;
    }

    public function getCreated() : \DateTimeInterface
    {
        return $this->created;
    }

    public function isUpdated() : bool
    {
        return !is_null($this->getUpdated());
    }

    public function getUpdated() : ?\DateTimeInterface
    {
        return $this->updated;
    }

    /** @throws  \DomainException */
    public function markAsUpdated(int $fileSize) : void
    {
        if ($this->isRemoved()) {
            throw new \DomainException('Cannot update a removed document');
        }
        $this->updated = new \DateTimeImmutable();
        $this->fileSize = $fileSize;
    }

    public function isRemoved() : bool
    {
        return !is_null($this->getRemoved());
    }

    public function getRemoved() : ?\DateTimeInterface
    {
        return $this->removed;
    }

    /** @throws  \DomainException */
    public function markAsRemoved() : void
    {
        if ($this->isRemoved()) {
            throw new \DomainException('Cannot remove an already removed document');
        }
        $this->removed = new \DateTimeImmutable();
        $this->fileSize = 0;
    }
}
