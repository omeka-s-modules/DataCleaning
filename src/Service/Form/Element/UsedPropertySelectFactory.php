<?php
namespace DataCleaning\Service\Form\Element;

use Interop\Container\ContainerInterface;
use DataCleaning\Form\Element\UsedPropertySelect;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UsedPropertySelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new UsedPropertySelect;
        $element->setEntityManager($services->get('Omeka\EntityManager'));
        return $element;
    }
}
