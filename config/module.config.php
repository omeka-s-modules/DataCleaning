<?php
namespace DataCleaning;

use Laminas\Router\Http\Segment;

return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => sprintf('%s/../language', __DIR__),
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            sprintf('%s/../view', __DIR__),
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'DataCleaning\Controller\Admin\Index' => Controller\Admin\IndexController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'dataCleaning' => Service\ControllerPlugin\DataCleaningFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'DataCleaning\Form\Element\DataTypeSelect' => Service\Form\Element\DataTypeSelectFactory::class,
            'DataCleaning\Form\Element\UsedPropertySelect' => Service\Form\Element\UsedPropertySelectFactory::class,
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Data Cleaning', // @translate
                'route' => 'admin/data-cleaning',
                'resource' => 'DataCleaning\Controller\Admin\Index',
                'privilege' => 'index',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'data-cleaning' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/data-cleaning/:controller[/:action]',
                             'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'DataCleaning\Controller\Admin',
                                'controller' => 'index',
                                'action' => 'index',
                            ],
                       ],
                    ],
                ],
            ],
        ],
    ],
];
