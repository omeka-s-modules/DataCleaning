<?php
namespace DataCleaning\Form\Element;

use DataCleaning\DataType\Unknown;
use Doctrine\ORM\EntityManager;
use Omeka\DataType\Manager as DataTypeManager;
use Laminas\Form\Element\Select;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class DataTypeSelect extends Select
{
    protected $dataTypeManager;
    protected $entityManager;

    public function setDataTypeManager(DataTypeManager $dataTypeManager)
    {
        $this->dataTypeManager = $dataTypeManager;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getValueOptions()
    {
        if ($this->getOption('exclude_unused_data_types')) {
            // Only include data types that are used in the value table. Note
            // that it's possible for values to have an unregistered data type,
            // so we must account for it below.
            $query = $this->entityManager->createQuery('SELECT DISTINCT v.type FROM Omeka\Entity\Value v');
            $dataTypeNames = array_column($query->getResult(), 'type');
        } else {
            $dataTypeNames = $this->dataTypeManager->getRegisteredNames();
        }

        $options = [];
        $optgroupOptions = [];
        foreach ($dataTypeNames as $dataTypeName) {
            try {
                $dataType = $this->dataTypeManager->get($dataTypeName);
            } catch (ServiceNotFoundException $e) {
                // Account for unregistered data types.
                $dataType = new Unknown($dataTypeName);
            }
            $label = $dataType->getLabel();
            if ($optgroupLabel = $dataType->getOptgroupLabel()) {
                $optgroupKey = md5($optgroupLabel);
                $optionsVal = in_array($dataTypeName, ['resource', 'resource:item', 'resource:itemset', 'resource:media']) ? 'options' : 'optgroupOptions';
                if (!isset(${$optionsVal}[$optgroupKey])) {
                    ${$optionsVal}[$optgroupKey] = [
                        'label' => $optgroupLabel,
                        'options' => [],
                    ];
                }
                ${$optionsVal}[$optgroupKey]['options'][$dataTypeName] = $label;
            } else {
                $options[$dataTypeName] = $label;
            }
        }
        return array_merge($options, $optgroupOptions);
    }
}
