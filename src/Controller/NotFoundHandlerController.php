<?php declare(strict_types = 1);

namespace jschreuder\DocStore\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class NotFoundHandlerController implements ControllerInterface
{
    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse(
            [
                'message' => 'Not found: ' . $request->getUri()->getPath(),
            ],
            404
        );
    }
}
