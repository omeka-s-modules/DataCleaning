<?php
namespace DataCleaning\DataType;

use Omeka\DataType\DataTypeInterface;
use Omeka\Entity\Value;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Representation\ValueRepresentation;
use Laminas\View\Renderer\PhpRenderer;

class Unknown implements DataTypeInterface
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->name;
    }

    public function getOptgroupLabel()
    {
    }

    public function prepareForm(PhpRenderer $view)
    {
    }

    public function form(PhpRenderer $view)
    {
    }

    public function isValid(array $valueObject)
    {
        return true;
    }

    public function hydrate(array $valueObject, Value $value, AbstractEntityAdapter $adapter)
    {
    }

    public function render(PhpRenderer $view, ValueRepresentation $value)
    {
    }

    public function toString(ValueRepresentation $value)
    {
    }

    public function getJsonLd(ValueRepresentation $value)
    {
    }

    public function getFulltextText(PhpRenderer $view, ValueRepresentation $value)
    {
    }
}
