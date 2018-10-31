<?php declare(strict_types = 1);

namespace jschreuder\DocStore;

use jschreuder\DocStore\Repository\DocumentRepository;
use jschreuder\DocStore\Repository\PublicationRepository;
use jschreuder\DocStore\StorageEngine\StorageEngineCollection;
use jschreuder\DocStore\StorageEngine\StorageEngineInterface;
use jschreuder\DocStore\Type\TypeCollection;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Exception\LogicException;

class DefaultServicesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['db'] = function (Container $container) {
            return new \PDO(
                $container['db.dsn'] . ';dbname=' . $container['db.dbname'],
                $container['db.user'],
                $container['db.pass'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
        };

        $container['repository.documents'] = function (Container $container) {
            return new DocumentRepository($container['db'], $container['repository.publications']);
        };

        $container['repository.publications'] = function (Container $container) {
            return new PublicationRepository($container['db']);
        };

        $container['types'] = function (Container $container) {
            return new TypeCollection(...$container['config']['types']);
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
            return new StorageEngineCollection($storageEngines);
        };
    }
}
