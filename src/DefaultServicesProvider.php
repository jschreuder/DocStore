<?php declare(strict_types = 1);

namespace jschreuder\DocStore;

use jschreuder\DocStore\Controller\Error\ErrorHandlerController;
use jschreuder\DocStore\Controller\Error\NotFoundHandlerController;
use jschreuder\DocStore\Repository\DocumentRepository;
use jschreuder\DocStore\Repository\PublicationRepository;
use jschreuder\DocStore\StorageEngine\StorageEngineCollection;
use jschreuder\DocStore\StorageEngine\StorageEngineInterface;
use jschreuder\DocStore\PublicationType\PublicationTypeCollection;
use jschreuder\Middle\ApplicationStack;
use jschreuder\Middle\Controller\ControllerRunner;
use jschreuder\Middle\Exception\ValidationFailedException;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\Middle\ServerMiddleware\ErrorHandlerMiddleware;
use jschreuder\Middle\ServerMiddleware\JsonRequestParserMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestFilterMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestValidatorMiddleware;
use jschreuder\Middle\ServerMiddleware\RoutingMiddleware;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Exception\LogicException;
use Zend\Diactoros\Response\JsonResponse;

class DefaultServicesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['app'] = function (Container $container) {
            return new ApplicationStack(
                new ControllerRunner(),
                new RequestValidatorMiddleware($container['requestValidator.errorHandler']),
                new RequestFilterMiddleware(),
                new JsonRequestParserMiddleware(),
                new RoutingMiddleware(
                    $container['app.router'],
                    $container['app.error_handlers.404']
                ),
                new ErrorHandlerMiddleware(
                    $container['logger'],
                    $container['app.error_handlers.500']
                )
            );
        };

        $container['logger'] = $container['config']['logger'];

        $container['app.router'] = function (Container $container) {
            return new SymfonyRouter($container['config']['site.url']);
        };

        $container['app.url_generator'] = function (Container $container) {
            /** @var  RouterInterface $router */
            $router = $container['app.router'];
            return $router->getGenerator();
        };

        $container['app.error_handlers.404'] = function () {
            return new NotFoundHandlerController();
        };

        $container['app.error_handlers.500'] = function (Container $container) {
            return new ErrorHandlerController($container['logger']);
        };

        $container['requestValidator.errorHandler'] = $container->protect(function (
            ServerRequestInterface $request,
            ValidationFailedException $validationFailedException
        ) : ResponseInterface {
            return new JsonResponse([
                'validation_errors' => array_map(function (array $errors) {
                    return array_keys($errors);
                }, $validationFailedException->getValidationErrors()),
            ], 400);
        });

        $container['db'] = function (Container $container) {
            $config = $container['config'];
            return new \PDO(
                $config['db.dsn'] . ';dbname=' . $config['db.dbname'],
                $config['db.user'],
                $config['db.pass'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
        };

        $container['repository.documents'] = function (Container $container) {
            return new DocumentRepository(
                $container['db'],
                $container['repository.publications'],
                $container['storage_engines']
            );
        };

        $container['repository.publications'] = function (Container $container) {
            return new PublicationRepository($container['db'], $container['publication_types']);
        };

        $container['publication_types'] = function (Container $container) {
            return new PublicationTypeCollection(...$container['config']['publication_types']);
        };

        $container['storage_engines'] = function (Container $container) {
            $storageEngines = [];
            foreach ($container['config']['storage_engines'] as $factory) {
                if (!is_callable($factory)) {
                    throw new LogicException('Storage engines must be configured as callable factories.');
                }
                $storageEngine = $factory($container);
                if (!$storageEngine instanceof StorageEngineInterface) {
                    throw new LogicException('Configured storage engine factory did not return a valid instance.');
                }
                $storageEngines[$storageEngine->getName()] = $storageEngine;
            }
            return new StorageEngineCollection(...$storageEngines);
        };
    }
}
