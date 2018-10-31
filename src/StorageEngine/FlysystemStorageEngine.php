<?php declare(strict_types = 1);

namespace jschreuder\DocStore\StorageEngine;

use jschreuder\DocStore\Entity\Document;
use jschreuder\DocStore\Repository\DocumentRepository;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

class FlysystemStorageEngine implements StorageEngineInterface
{
    /** @var  string */
    private $name;

    /** @var  string */
    private $title;

    /** @var  Filesystem */
    private $flysystem;

    /** @var  DocumentRepository */
    private $documentRepository;

    public function __construct(
        string $name,
        string $title,
        Filesystem $flysystem,
        DocumentRepository $documentRepository
    ) {
        $this->name = $name;
        $this->title = $title;
        $this->flysystem = $flysystem;
        $this->documentRepository = $documentRepository;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    private function documentToPath(Document $document)
    {
        return $document->getId()->toString();
    }

    /** @throws  FileExistsException */
    public function create(Document $document, $stream) : void
    {
        $this->flysystem->writeStream($this->documentToPath($document), $stream);
    }

    /** @throws  FileNotFoundException */
    public function read(Document $document)
    {
        return $this->flysystem->readStream($this->documentToPath($document));
    }

    /** @throws  FileNotFoundException */
    public function update(Document $document, $stream) : void
    {
        $this->flysystem->updateStream($this->documentToPath($document), $stream);
        $document->markAsUpdated($this->flysystem->getSize($this->documentToPath($document)));
        $this->documentRepository->updateDocument($document);
    }

    /** @throws  FileNotFoundException */
    public function delete(Document $document) : void
    {
        $this->flysystem->delete($this->documentToPath($document));
        $document->markAsRemoved();
        $this->documentRepository->deleteDocument($document);
    }
}
