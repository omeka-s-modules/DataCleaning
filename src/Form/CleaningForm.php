<?php
namespace DataCleaning\Form;

use DataCleaning\Form\Element as DataCleaningElement;
use Omeka\Form\Element as OmekaElement;
use Zend\Form\Element as ZendElement;
use Zend\Form\Form;

class CleaningForm extends Form
{
    public function init()
    {
        // Increase CSRF timeout from 1 to 2 hours.
        $csrfElement = $this->get('cleaningform_csrf');
        $csrfOptions = $csrfElement->getOptions();
        $csrfOptions['csrf_options']['timeout'] = 7200;
        $csrfElement->setOptions($csrfOptions);

        $this->setAttribute('id', 'cleaning-form');
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
            'name' => 'audit_column',
            'attributes' => [
                'id' => 'audit_column',
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
            'name' => 'property_id',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'data_type_name',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'audit_column',
            'required' => true,
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
