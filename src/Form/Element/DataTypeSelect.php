<?php
namespace DataCleaning\Form\Element;

use Omeka\DataType\Manager as DataTypeManager;
use Zend\Form\Element\Select;

class DataTypeSelect extends Select
{
    protected $dataTypeManager;

    public function setDataTypeManager(DataTypeManager $dataTypeManager)
    {
        $this->dataTypeManager = $dataTypeManager;
    }

    public function getValueOptions()
    {
        $options = [];
        $optgroupOptions = [];
        foreach ($this->dataTypeManager->getRegisteredNames() as $dataTypeName) {
            $dataType = $this->dataTypeManager->get($dataTypeName);
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
