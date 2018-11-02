<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Controller;

use jschreuder\DocStore\Repository\PublicationRepository;
use jschreuder\Middle\Controller\ControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ListPublicationsController implements ControllerInterface
{
    /** @var  PublicationRepository */
    private $publicationRepository;

    public function __construct(PublicationRepository $publicationRepository)
    {
        $this->publicationRepository = $publicationRepository;
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $params = (array) $request->getQueryParams();
        $listType = isset($params['list_type']) ? strval($params['list_type']) : '';
        $limit = isset($params['limit']) ? intval($params['limit']) : 25;
        $offset = isset($params['offset']) ? intval($params['offset']) : 0;
        switch ($listType) {
            case '':
            case 'published':
                $publications = $this->publicationRepository->readPublishedPublications($limit, $offset);
                break;
            case 'unpublished':
                $publications = $this->publicationRepository->readUnpublishedPublications($limit, $offset);
                break;
            case 'removed':
                $publications = $this->publicationRepository->readRemovedPublications($limit, $offset);
                break;
            default:
                throw new \DomainException('Invalid list type given: ' . $params['list_type']);
        }

        $publicationData = [];
        foreach ($publications as $publication) {
            $publicationData[] = [
                'publication_id' => $publication->getId()->toString(),
                'publication_type' => $publication->getType(),
                'title' => $publication->getTitle(),
                'description' => $publication->getDescription(),
                'created' => $publication->getCreated()->format('Y-m-d H:i:s'),
                'published' => $publication->getPublished() ? $publication->getPublished()->format('Y-m-d H:i:s') : null,
                'removed' => $publication->getRemoved() ? $publication->getRemoved()->format('Y-m-d H:i:s') : null,
            ];
        }

        return new JsonResponse($publicationData);
    }
}
