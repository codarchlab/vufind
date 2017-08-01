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
            'recordlink' => 'Zenon\View\Helper\Root\Factory::getRecordLink',
            'datetime' => 'Zenon\View\Helper\Root\Factory::getDateTime'
        ),
        'invokables' => array(
            'resultfeed' => 'Zenon\View\Helper\Root\ResultFeed'
        )
    ),
    'favicon' => 'favicon.ico'
);
