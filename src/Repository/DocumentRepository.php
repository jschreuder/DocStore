<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Repository;

use jschreuder\DocStore\Entity\Document;
use jschreuder\DocStore\Entity\Publication;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DocumentRepository
{
    /** @var  \PDO */
    private $db;

    /** @var  PublicationRepository */
    private $publicationRepository;

    public function __construct(\PDO $db, PublicationRepository $publicationRepository)
    {
        $this->db = $db;
        $this->publicationRepository = $publicationRepository;
    }

    public function createDocument(Document $document) : void
    {
        $query = $this->db->prepare("
            INSERT INTO `documents`
                (`document_id`, `publication_id`, `document_type`, `storage_engine`, `title`, `filename`, `filesize`, 
                 `mime_type`, `created`, `updated`, `removed`)
            VALUES
                (:document_id, :publication_id, :document_type, :storage_engine, :title, :filename, :filesize, 
                 :mime_type, :created, :updated, :removed)
        ");
        $query->execute([
            'document_id' => $document->getId()->getBytes(),
            'publication_id' => $document->getPublication()->getId()->getBytes(),
            'document_type' => $document->getType(),
            'storage_engine' => $document->getStorageEngine(),
            'title' => $document->getTitle(),
            'filename' => $document->getFileName(),
            'filesize' => $document->getFileSize(),
            'mime_type' => $document->getMimeType(),
            'created' => $document->getCreated()->format('Y-m-d H:i:s'),
            'updated' => $document->getUpdated()->format('Y-m-d H:i:s'),
            'removed' => $document->getUpdated()->format('Y-m-d H:i:s'),
        ]);
    }

    private function documentFromRow(array $row, Publication $publication) : Document
    {
        return new Document(
            Uuid::fromBytes($row['document_id']),
            $publication,
            $row['document_type'],
            $row['storage_engine'],
            $row['title'],
            $row['filename'],
            intval($row['filesize']),
            $row['mime_type'],
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['created']),
            $row['updated'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['updated']) : null,
            $row['removed'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['removed']) : null
        );
    }

    /** @throws  \OutOfBoundsException */
    public function readDocument(UuidInterface $documentId) : Document
    {
        $query = $this->db->prepare("
            SELECT `document_id`, `document_type`, `storage_engine`, `title`, `filename`, `filesize`, `mime_type`, 
                `created`, `updated`, `removed`
            FROM `documents`
            WHERE `document_id` = :document_id
        ");
        $query->execute(['document_id' => $documentId->getBytes()]);
        if ($query->rowCount() === 0) {
            throw new \OutOfBoundsException('No document found with ID: ' . $documentId->toString());
        }

        $row = $query->fetch(\PDO::FETCH_ASSOC);
        return $this->documentFromRow(
            $row,
            $this->publicationRepository->readPublication(Uuid::fromBytes($row['publication_id']))
        );
    }

    /** @return  Document[] */
    public function readPublicationDocuments(Publication $publication) : array
    {
        $query = $this->db->prepare("
            SELECT `document_id`, `document_type`, `storage_engine`, `title`, `filename`, `filesize`, `mime_type`, 
                `created`, `updated`, `removed`
            FROM `documents`
            WHERE `publication_id` = :publication_id
        ");
        $query->execute(['publication_id' => $publication->getId()->getBytes()]);

        $documents = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $documents[] = $this->documentFromRow($row, $publication);
        }
        return $documents;
    }

    /** @throws  \DomainException */
    public function updateDocument(Document $document) : void
    {
        if (!$document->isUpdated()) {
            throw new \DomainException('Cannot update a document which was not updated');
        }

        $query = $this->db->prepare("
            UPDATE `documents`
            SET `filesize` = :filesize, `updated` = :updated
            WHERE `document_id` = :document_id
        ");
        $query->execute([
            'document_id' => $document->getId()->getBytes(),
            'filesize' => $document->getFileSize(),
            'updated' => $document->getUpdated()->format('Y-m-d H:i:s'),
        ]);
    }

    /** @throws  \DomainException */
    public function deleteDocument(Document $document) : void
    {
        if (!$document->isRemoved()) {
            throw new \DomainException('Cannot delete a document which was not marked as removed');
        }

        $query = $this->db->prepare("
            UPDATE `documents`
            SET `filesize` = :filesize, `removed` = :removed
            WHERE `document_id` = :document_id
        ");
        $query->execute([
            'document_id' => $document->getId()->getBytes(),
            'filesize' => $document->getFileSize(),
            'removed' => $document->getRemoved()->format('Y-m-d H:i:s'),
        ]);
    }
}
