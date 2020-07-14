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
            'name' => 'item_query',
            'attributes' => [
                'id' => 'item_query',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'audit_column',
            'attributes' => [
                'id' => 'audit_column',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'property_id',
            'attributes' => [
                'id' => 'property_id',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'data_type_name',
            'attributes' => [
                'id' => 'data_type_name',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'target_audit_column',
            'attributes' => [
                'id' => 'target_audit_column',
            ],
       ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'target_property_id',
            'attributes' => [
                'id' => 'target_property_id',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'target_data_type_name',
            'attributes' => [
                'id' => 'target_data_type_name',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'corrections',
            'attributes' => [
                'id' => 'corrections',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'removals',
            'attributes' => [
                'id' => 'removals',
            ],
        ]);
        $this->add([
            'type' => ZendElement\Hidden::class,
            'name' => 'item_ids',
            'attributes' => [
                'id' => 'item_ids',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'item_query',
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
            'name' => 'item_ids',
            'required' => true,
        ]);
    }
}
