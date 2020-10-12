<?php
namespace DataCleaning\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use DataCleaning\ControllerPlugin\DataCleaning;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DataCleaningFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new DataCleaning($services);
    }
}
