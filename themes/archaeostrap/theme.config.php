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
            'record' => 'Zenon\View\Helper\Root\Factory::getRecord',
            'recordlink' => 'Zenon\View\Helper\Root\Factory::getRecordLink',
            'datetime' => 'Zenon\View\Helper\Root\Factory::getDateTime'
        ),
        'invokables' => array(
            'resultfeed' => 'Zenon\View\Helper\Root\ResultFeed'
        )
    ),
    'favicon' => 'favicon.ico'
);
