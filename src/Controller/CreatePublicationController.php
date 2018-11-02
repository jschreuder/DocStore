<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Controller;

use jschreuder\DocStore\Entity\Publication;
use jschreuder\DocStore\PublicationType\PublicationTypeCollection;
use jschreuder\DocStore\Repository\PublicationRepository;
use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Exception\ValidationFailedException;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class CreatePublicationController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  PublicationRepository */
    private $publicationRepository;

    /** @var  PublicationTypeCollection */
    private $publicationTypes;

    public function __construct(
        PublicationRepository $publicationRepository,
        PublicationTypeCollection $publicationTypes
    ) {
        $this->publicationRepository = $publicationRepository;
        $this->publicationTypes = $publicationTypes;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $filter = new Filter();
        $filter->value('title')->string()->trim()->stripHtml();
        $filter->value('description')->string()->trim()->stripHtml();

        return $request->withParsedBody($filter->filter((array) $request->getParsedBody()));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('type')->inArray($this->publicationTypes->getTypeNames());
        $validator->required('title')->lengthBetween(1, 255);
        $validator->required('description')->lengthBetween(1, 65535);

        $result = $validator->validate((array) $request->getParsedBody());
        if ($result->isNotValid()) {
            throw new ValidationFailedException($result->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $publicationData = (array) $request->getParsedBody();
        $publication = new Publication(
            Uuid::uuid4(),
            $publicationData['type'],
            $publicationData['title'],
            $publicationData['description'],
            new \DateTimeImmutable()
        );
        $this->publicationRepository->createPublication($publication);

        return new JsonResponse([
            'publication_id' => $publication->getId()->toString(),
            'publication_type' => $publication->getType(),
            'title' => $publication->getTitle(),
            'description' => $publication->getDescription(),
            'created' => $publication->getCreated()->format('Y-m-d H:i:s'),
            'published' => null,
            'removed' => null,
        ]);
    }
}
