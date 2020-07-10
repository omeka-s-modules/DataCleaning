<?php
namespace DataCleaning\Form;

use DataCleaning\Form\Element as DataCleaningElement;
use Omeka\Form\Element as OmekaElement;
use Zend\Form\Element as ZendElement;
use Zend\Form\Form;

class PrepareAuditForm extends Form
{
    public function init()
    {
        // Increase CSRF timeout from 1 to 2 hours.
        $csrfElement = $this->get('prepareauditform_csrf');
        $csrfOptions = $csrfElement->getOptions();
        $csrfOptions['csrf_options']['timeout'] = 7200;
        $csrfElement->setOptions($csrfOptions);

        $this->add([
            'type' => ZendElement\Text::class,
            'name' => 'item_query',
            'options' => [
                'label' => 'Item query', // @translate
            ],
        ]);
        $this->add([
            'type' => ZendElement\Select::class,
            'name' => 'audit_column',
            'options' => [
                'label' => 'Audit column', // @translate
                'show_required' => true,
                'value_options' => [
                    'value' => 'value', // @translate
                    'uri' => 'uri', // @translate
                    'value_resource_id' => 'value_resource_id', // @translate
                ],
            ],
        ]);
        $this->add([
            'type' => OmekaElement\PropertySelect::class,
            'name' => 'property_id',
            'options' => [
                'label' => 'Property', // @translate
                'empty_option' => '',
                'show_required' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a property', // @translate
            ],
        ]);
        $this->add([
            'type' => DataCleaningElement\DataTypeSelect::class,
            'name' => 'data_type_name',
            'options' => [
                'label' => 'Data type', // @translate
                'empty_option' => '',
                'show_required' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a data type', // @translate
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'audit_column',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'property_id',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'data_type_name',
            'required' => true,
        ]);
    }
}
