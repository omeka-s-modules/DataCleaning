<?php
namespace DataCleaning\Form;

use DataCleaning\Form\Element as DataCleaningElement;
use Omeka\Form\Element as OmekaElement;
use Zend\Form\Element as ZendElement;
use Zend\Form\Form;

class AuditForm extends Form
{
    public function init()
    {
        // Increase CSRF timeout from 1 to 2 hours.
        $csrfElement = $this->get('auditform_csrf');
        $csrfOptions = $csrfElement->getOptions();
        $csrfOptions['csrf_options']['timeout'] = 7200;
        $csrfElement->setOptions($csrfOptions);

        $this->setAttribute('id', 'audit-form');
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'resource_name',
            'options' => [
                'label' => 'Resource type',
            ],
            'attributes' => [
                'id' => 'resource_name',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'resource_query',
            'options' => [
                'label' => 'Resource query',
            ],
            'attributes' => [
                'id' => 'resource_query',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'audit_column',
            'options' => [
                'label' => 'Audit column',
            ],
            'attributes' => [
                'id' => 'audit_column',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'property_id',
            'options' => [
                'label' => 'Property',
            ],
            'attributes' => [
                'id' => 'property_id',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'data_type_name',
            'options' => [
                'label' => 'Data type',
            ],
            'attributes' => [
                'id' => 'data_type_name',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'target_audit_column',
            'options' => [
                'label' => 'Target audit column',
            ],
            'attributes' => [
                'id' => 'target_audit_column',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'target_property_id',
            'options' => [
                'label' => 'Target property',
            ],
            'attributes' => [
                'id' => 'target_property_id',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'target_data_type_name',
            'options' => [
                'label' => 'Target data type',
            ],
            'attributes' => [
                'id' => 'target_data_type_name',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'corrections',
            'options' => [
                'label' => 'Corrections',
            ],
            'attributes' => [
                'id' => 'corrections',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'removals',
            'options' => [
                'label' => 'Removals',
            ],
            'attributes' => [
                'id' => 'removals',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'resource_ids',
            'options' => [
                'label' => 'Resource IDs',
            ],
            'attributes' => [
                'id' => 'resource_ids',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'resource_name',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'resource_query',
            'required' => true,
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
        $inputFilter->add([
            'name' => 'target_audit_column',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'target_property_id',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'target_data_type_name',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'corrections',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'removals',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'resource_ids',
            'required' => true,
        ]);
    }
}
