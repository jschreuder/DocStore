<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Entity;

use Ramsey\Uuid\UuidInterface;

class Publication
{
    /** @var  UuidInterface */
    private $id;

    /** @var  string */
    private $type;

    /** @var  string */
    private $title;

    /** @var  string */
    private $description;

    /** @var  \DateTimeInterface */
    private $created;

    /** @var  ?\DateTimeInterface */
    private $published;

    /** @var  ?\DateTimeInterface */
    private $removed;

    public function __construct(
        UuidInterface $id,
        string $type,
        string $title,
        string $description,
        \DateTimeInterface $created,
        ?\DateTimeInterface $published = null,
        ?\DateTimeInterface $removed = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->created = $created;
        $this->published = $published;
        $this->removed = $removed;
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /** @throws  \DomainException */
    public function setTitle(string $title) : void
    {
        if ($this->isPublished()) {
            throw new \DomainException('Cannot modify title after publication.');
        }
        $this->title = $title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    /** @throws  \DomainException */
    public function setDescription(string $description) : void
    {
        if ($this->isPublished()) {
            throw new \DomainException('Cannot modify description after publication.');
        }
        $this->description = $description;
    }

    public function getCreated() : \DateTimeInterface
    {
        return $this->created;
    }

    public function isPublished() : bool
    {
        return !is_null($this->published);
    }

    public function getPublished() : ?\DateTimeInterface
    {
        return $this->published;
    }

    /** @throws  \DomainException */
    public function markAsPublished() : void
    {
        if ($this->published) {
            throw new \DomainException('Already marked as published, cannot publish twice.');
        }
        $this->published = new \DateTimeImmutable();
    }

    public function isRemoved() : bool
    {
        return !is_null($this->removed);
    }

    public function getRemoved() : ?\DateTimeInterface
    {
        return $this->removed;
    }

    /** @throws  \DomainException */
    public function markAsRemoved() : void
    {
        if ($this->published) {
            throw new \DomainException('Already marked as removed, cannot remove twice.');
        }
        $this->removed = new \DateTimeImmutable();
    }
}
