<?php
namespace Zenon\Module\Config;

$config = [

	'controllers' => [
        'invokables' => [
            'thesaurus' => 'Zenon\Controller\ThesaurusController',
        ],
    ],

    'service_manager' => [
        'factories' => [
        	'VuFind\Mailer' => 'Zenon\Mailer\Factory',
        ]
    ],

	'vufind' => [
		'plugin_managers' => [
			'recorddriver' => [
				'factories' => [
					'solrmarc' => 'Zenon\RecordDriver\Factory::getSolrMarc',
					'solrauth' => 'Zenon\RecordDriver\Factory::getSolrAuth',
				]
			]
		],

		// This section controls which tabs are used for which record driver classes.
        // Each sub-array is a map from a tab name (as used in a record URL) to a tab
        // service (found in recordtab_plugin_manager, below).  If a particular record
        // driver is not defined here, it will inherit configuration from a configured
        // parent class.  The defaultTab setting may be used to specify the default
        // active tab; if null, the value from the relevant .ini file will be used.
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrMarc' => [
                'tabs' => [
                    'Holdings' => 'HoldingsILS', 'Description' => 'Description',
                    'TOC' => 'TOC', 'UserComments' => 'UserComments',
                    'Reviews' => 'Reviews', 'Excerpt' => 'Excerpt',
                    'Preview' => 'preview',
                    'HierarchyTree' => 'HierarchyTree', 'Map' => 'Map',
                    'Similar' => null,
                    'Details' => 'StaffViewMARC',
                ],
                'defaultTab' => null,
            ]
        ]

	]

];

return $config;

?>