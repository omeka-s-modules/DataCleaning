<?php
namespace DataCleaning\Form;

use DataCleaning\Form\Element as DataCleaningElement;
use Omeka\Form\Element as OmekaElement;
use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

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
            'type' => LaminasElement\Select::class,
            'name' => 'resource_name',
            'options' => [
                'label' => 'Resource type', // @translate
                'info' => 'Select the resource type to audit.', // @translate
                'show_required' => true,
                'value_options' => [
                    'items' => 'Items',
                    'item_sets' => 'Item sets',
                    'media' => 'Media',
                ],
            ],
            'attributes' => [
                'class' => 'chosen-select',
            ],
        ]);
        $this->add([
            'type' => OmekaElement\Query::class,
            'name' => 'resource_query',
            'options' => [
                'label' => 'Resource query', // @translate
                'info' => 'Enter a search query to filter the resources. No query means all resources.', // @translate
            ],
        ]);
        $this->add([
            'type' => DataCleaningElement\UsedPropertySelect::class,
            'name' => 'property_id',
            'options' => [
                'label' => 'Property', // @translate
                'info' => 'Select the property to audit.', // @translate
                'empty_option' => '',
                'show_required' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a property', // @translate
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'audit_column',
            'options' => [
                'label' => 'Audit column', // @translate
                'info' => 'Select the column to audit.', // @translate
                'empty_option' => '',
                'show_required' => true,
                'value_options' => [
                    'value' => 'value',
                    'uri' => 'uri',
                    'value_resource_id' => 'value_resource_id',
                ],
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select an audit column', // @translate
            ],
        ]);
        $this->add([
            'type' => DataCleaningElement\DataTypeSelect::class,
            'name' => 'data_type_name',
            'options' => [
                'label' => 'Data type', // @translate
                'info' => 'Select the data type to audit.', // @translate
                'empty_option' => '',
                'show_required' => true,
                'exclude_unused_data_types' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a data type', // @translate
            ],
        ]);
        $this->add([
            'type' => Fieldset::class,
            'name' => 'advanced',
            'options' => [
                'label' => 'Advanced', // @translate
            ],
        ]);
        $this->get('advanced')->add([
            'type' => OmekaElement\PropertySelect::class,
            'name' => 'target_property_id',
            'options' => [
                'label' => 'Target property', // @translate
                'info' => 'Select the property that corrections will be stored as. You do not need to select a target if it is the same as the property above.', // @translate
                'empty_option' => '',
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a target property', // @translate
            ],
        ]);
        $this->get('advanced')->add([
            'type' => LaminasElement\Select::class,
            'name' => 'target_audit_column',
            'options' => [
                'label' => 'Target audit column', // @translate
                'info' => 'Select the column that corrections will be stored in. You do not need to select a target if it is the same as the audit column above.', // @translate
                'empty_option' => '',
                'value_options' => [
                    'value' => 'value',
                    'uri' => 'uri',
                    'value_resource_id' => 'value_resource_id',
                ],
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a target audit column', // @translate
            ],
        ]);
        $this->get('advanced')->add([
            'type' => DataCleaningElement\DataTypeSelect::class,
            'name' => 'target_data_type_name',
            'options' => [
                'label' => 'Target data type', // @translate
                'info' => 'Select the data type that corrections will be stored as. You do not need to select a target if it is the same as the data type above.', // @translate
                'empty_option' => '',
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a target data type', // @translate
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'resource_name',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'resource_query',
            'required' => false,
        ]);
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
        $inputFilter->get('advanced')->add([
            'name' => 'target_audit_column',
            'required' => false,
        ]);
        $inputFilter->get('advanced')->add([
            'name' => 'target_property_id',
            'required' => false,
        ]);
        $inputFilter->get('advanced')->add([
            'name' => 'target_data_type_name',
            'required' => false,
        ]);
    }
}
