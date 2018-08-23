<?php
return array(
    'extends' => 'bootstrap3',
    'css' => array(
    	'idai-components.min.css',
    	'custom.css'
    ),
    'js' => array(
        'vendor/bootstrap-slider.min.js'
    ),
    'helpers' => array(
        'factories' => array(
            'citation' => 'Zenon\View\Helper\Root\Factory::getCitation',
            'datetime' => 'Zenon\View\Helper\Root\Factory::getDateTime',
            'record' => 'Zenon\View\Helper\Root\Factory::getRecord',
            'recordlink' => 'Zenon\View\Helper\Root\Factory::getRecordLink',
        ),
        'invokables' => array(
            'resultfeed' => 'Zenon\View\Helper\Root\ResultFeed'
        )
    ),
    'favicon' => 'favicon.ico'
);
