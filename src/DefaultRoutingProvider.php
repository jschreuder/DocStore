<?php declare(strict_types = 1);

namespace jschreuder\DocStore;

use jschreuder\DocStore\Controller\CreatePublicationController;
use jschreuder\DocStore\Controller\ListPublicationsController;
use jschreuder\Middle\Controller\CallableController;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\RoutingProviderInterface;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

class DefaultRoutingProvider implements RoutingProviderInterface
{
    /** @var  Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function registerRoutes(RouterInterface $router): void
    {
        $router->get('home', '/', function () {
            return CallableController::fromCallable(function (RequestInterface $request) : ResponseInterface {
                return new JsonResponse(['test' => true]);
            });
        });

        $router->post('publication.create', '/publications', function () {
            return new CreatePublicationController(
                $this->container['repository.publications'],
                $this->container['publication_types']
            );
        });
        $router->get('publication.list', '/publications', function () {
            return new ListPublicationsController($this->container['repository.publications']);
        });
    }
}
