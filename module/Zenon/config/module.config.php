<?php
namespace Zenon\Module\Config;

$config = [

	'controllers' => [
        'factories' => [
            'Zenon\Controller\CartController' => 'VuFind\Controller\CartControllerFactory',
            'Zenon\Controller\RecordsController' => 'VuFind\Controller\AbstractBaseFactory',
            'Zenon\Controller\ThesaurusController' =>  'VuFind\Controller\AjaxControllerFactory',
        ],
        'aliases' => [
            'Cart' => 'Zenon\Controller\CartController',
            'cart' => 'Zenon\Controller\CartController',
            'Records' => 'Zenon\Controller\RecordsController',
            'records' => 'Zenon\Controller\RecordsController',
            'thesaurus' => 'Zenon\Controller\ThesaurusController',
        ]
    ],

    'service_manager' => [
        'allow_override' => true,
        'factories' => [
            'Zenon\RecordDriver\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            ],
        'aliases' => [
            'Zenon\RecordDriverPluginManager' => 'Zenon\RecordDriver\PluginManager',
            ]
        ],

	'vufind' => [
		'plugin_managers' => [
			'recorddriver' => [
				'factories' => [
					'Zenon\RecordDriver\SolrMarc' => 'Zenon\RecordDriver\SolrDefaultFactory',
                    'Zenon\RecordDriver\SolrAuthMarc' => 'VuFind\RecordDriver\SolrDefaultWithoutSearchServiceFactory',
				],
                'delegators' => [
                    'Zenon\RecordDriver\SolrMarc' => ['VuFind\RecordDriver\IlsAwareDelegatorFactory'],
                ],
                'aliases' => [
                    'solrmarc' => 'Zenon\RecordDriver\SolrMarc',
                    'solrauthmarc' => 'Zenon\RecordDriver\SolrAuthMarc',
                    'solrauth' => 'Zenon\RecordDriver\SolrAuthMarc', // legacy name
                ]
			],
            'recordtab' => [
                'factories' => [
                    'Zenon\RecordTab\Access' => 'Zenon\RecordTab\Factory::getAccess',
                ],
                'aliases' => [
                    'Access' => 'Zenon\RecordTab\Access',
                ]
            ],
		],

		// This section controls which tabs are used for which record driver classes.
        // Each sub-array is a map from a tab name (as used in a record URL) to a tab
        // service (found in recordtab_plugin_manager, below).  If a particular record
        // driver is not defined here, it will inherit configuration from a configured
        // parent class.  The defaultTab setting may be used to specify the default
        // active tab; if null, the value from the relevant .ini file will be used.
        'recorddriver_tabs' => [
            'Zenon\RecordDriver\SolrMarc' => [
                'tabs' => [
                    'Holdings' => 'HoldingsILS', 'Description' => 'Description',
                    'TOC' => 'TOC', 'UserComments' => 'UserComments',
                    'Reviews' => 'Reviews', 'Excerpt' => 'Excerpt',
                    'Preview' => 'preview',
                    'HierarchyTree' => 'HierarchyTree', 'Map' => 'Map',
                    'Similar' => null,
                    'Details' => 'StaffViewMARC',
                    'Access' => 'Access',
                ],
                'defaultTab' => null,
            ],
        ],
	],

    // Define static routes -- Controller/Action strings
    $staticRoutes = [
        'Records/Cite'
    ]
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;