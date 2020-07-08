<?php
namespace DataCleaning\Service\Form\Element;

use Interop\Container\ContainerInterface;
use DataCleaning\Form\Element\DataTypeSelect;
use Zend\ServiceManager\Factory\FactoryInterface;

class DataTypeSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new DataTypeSelect;
        $element->setDataTypeManager($services->get('Omeka\DataTypeManager'));
        return $element;
    }
}
