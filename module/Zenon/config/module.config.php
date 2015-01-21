<?php
namespace Zenon\Module\Config;

$config = array(

	'vufind' => array(
		'plugin_managers' => array(
			'recorddriver' => array(
				'factories' => array(
					'solrmarc' => 'Zenon\RecordDriver\Factory::getSolrMarc',
				)
			)
		)
	)

);

return $config;

?>