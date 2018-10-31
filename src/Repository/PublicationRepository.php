<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Repository;

use jschreuder\DocStore\Entity\Publication;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PublicationRepository
{
    /** @var  \PDO */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function createPublication(Publication $publication)
    {
        $query = $this->db->prepare("
            INSERT INTO `publications`
                (`publication_id`, `title`, `description`, `created`, `published`, `removed`)
            VALUES
                (:publication_id, :title, :description, :created, :published, :removed)
        ");
        $query->execute([
            'publication_id' => $publication->getId()->getBytes(),
            'title' => $publication->getTitle(),
            'description' => $publication->getDescription(),
            'created' => $publication->getCreated()->format('Y-m-d H:i:s'),
            'published' => $publication->getPublished() ? $publication->getPublished()->format('Y-m-d H:i:s') : null,
            'removed' => $publication->getRemoved() ? $publication->getRemoved()->format('Y-m-d H:i:s') : null,
        ]);
    }

    private function publicationFromRow(array $row) : Publication
    {
        return new Publication(
            Uuid::fromBytes($row['publication_id']),
            $row['title'],
            $row['description'],
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['created']),
            $row['published'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['published']) : null,
            $row['removed'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['removed']) : null
        );
    }

    /** @throws  \OutOfBoundsException */
    public function readPublication(UuidInterface $publicationId) : Publication
    {
        $query = $this->db->prepare("
            SELECT `publication_id`, `title`, `description`, `published`, `removed`
            FROM `publications`
            WHERE `publication_id` = :publication_id
        ");
        $query->execute(['publication_id' => $publicationId->getBytes()]);
        if ($query->rowCount() === 0) {
            throw new \OutOfBoundsException('No publication found with ID: ' . $publicationId->toString());
        }

        return $this->publicationFromRow($query->fetch(\PDO::FETCH_ASSOC));
    }

    /** @return  Publication[] */
    public function readUnpublishedPublications($limit = 25, $offset = 0) : array
    {
        $query = $this->db->prepare("
            SELECT `publication_id`, `title`, `description`, `published`, `removed`
            FROM `publications`
            WHERE `published` IS NULL AND `removed` IS NULL
            ORDER BY `created` DESC
            LIMIT :limit OFFSET :offset
        ");
        $query->execute([
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $publications = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $publications[] = $this->publicationFromRow($row);
        }
        return $publications;
    }

    /** @return  Publication[] */
    public function readPublishedPublications($limit = 25, $offset = 0) : array
    {
        $query = $this->db->prepare("
            SELECT `publication_id`, `title`, `description`, `published`, `removed`
            FROM `publications`
            WHERE `published` IS NOT NULL AND `removed` IS NULL
            ORDER BY `published` DESC
            LIMIT :limit OFFSET :offset
        ");
        $query->execute([
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $publications = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $publications[] = $this->publicationFromRow($row);
        }
        return $publications;
    }

    /** @return  Publication[] */
    public function readRemovedPublications($limit = 25, $offset = 0) : array
    {
        $query = $this->db->prepare("
            SELECT `publication_id`, `title`, `description`, `published`, `removed`
            FROM `publications`
            WHERE `removed` IS NOT NULL
            ORDER BY `removed` DESC
            LIMIT :limit OFFSET :offset
        ");
        $query->execute([
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $publications = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $publications[] = $this->publicationFromRow($row);
        }
        return $publications;
    }

    public function updatePublication(Publication $publication) : void
    {
        $query = $this->db->prepare("
            UPDATE `publications`
            SET `title` = :title, `description` = :description
            WHERE `publication_id` = :publication_id AND `published` IS NULL
        ");
        $query->execute([
            'publication_id' => $publication->getId()->getBytes(),
            'title' => $publication->getTitle(),
            'description' => $publication->getDescription(),
        ]);
    }

    /** @throws  \DomainException */
    public function publishPublication(Publication $publication) : void
    {
        if (!$publication->isPublished()) {
            throw new \DomainException('Cannot publish a publication that was not marked as published.');
        }
        $query = $this->db->prepare("
            UPDATE `publications`
            SET `published` = :published
            WHERE `publication_id` = :publication_id AND `published` IS NULL
        ");
        $query->execute([
            'publication_id' => $publication->getId()->getBytes(),
            'published' => $publication->getPublished()->format('Y-m-d H:i:s'),
        ]);
    }

    /** @throws  \DomainException */
    public function deletePublication(Publication $publication) : void
    {
        if (!$publication->isRemoved()) {
            throw new \DomainException('Cannot delete a publication which was not marked as removed');
        }

        $query = $this->db->prepare("
            UPDATE `publications`
            SET `removed` = :removed
            WHERE `publication_id` = :publication_id
        ");
        $query->execute([
            'publication_id' => $publication->getId()->getBytes(),
            'removed' => $publication->getRemoved()->format('Y-m-d H:i:s'),
        ]);
    }
}
