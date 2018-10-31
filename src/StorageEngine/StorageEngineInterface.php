<?php declare(strict_types = 1);

namespace jschreuder\DocStore\StorageEngine;

use jschreuder\DocStore\Entity\Document;
use jschreuder\DocStore\Entity\File;

interface StorageEngineInterface
{
    /** Returns the string representation for this type */
    public function getName() : string;

    /** Returns a human readable title for this type */
    public function getTitle() : string;

    /**
     * Store the document's contents as given in the stream
     *
     * @param  resource $stream
     */
    public function create(Document $document, $stream) : void;

    /**
     * Retrieve the document's contents and return it as a stream
     *
     * @return resource
     */
    public function read(Document $document);

    /**
     * Update document with given File's contents
     *
     * @param  resource $stream
     */
    public function update(Document $document, $stream) : void;

    /** Delete contents for the given document */
    public function delete(Document $document) : void;
}
