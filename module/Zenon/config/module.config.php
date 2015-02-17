<?php
namespace Zenon\Module\Config;

$config = array(

	'controllers' => array(
        'invokables' => array(
            'thesaurus' => 'Zenon\Controller\ThesaurusController',
        ),
    ),

    'service_manager' => array(
        'factories' => array(
        	'VuFind\Mailer' => 'Zenon\Mailer\Factory',
        )
    ),

	'vufind' => array(
		'plugin_managers' => array(
			'recorddriver' => array(
				'factories' => array(
					'solrmarc' => 'Zenon\RecordDriver\Factory::getSolrMarc',
					'solrauth' => 'Zenon\RecordDriver\Factory::getSolrAuth',
				)
			)
		)
	)

);

return $config;

?>