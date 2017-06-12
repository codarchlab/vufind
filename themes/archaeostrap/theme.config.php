<?php
return array(
    'extends' => 'bootstrap3',
    'css' => array(
    	'idai-components.min.css',
    	'custom.css'
    ),
    'helpers' => array(
        'invokables' => array(
            'resultfeed' => 'Zenon\View\Helper\Root\ResultFeed'
        )
    ),
    'favicon' => 'favicon.ico'
);
