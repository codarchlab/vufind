<?php
return [
    'extends' => 'bootstrap3',
    'css' => [
    	'idai-components.min.css',
    	'custom.css',
    ],
    'js' => [
        'vendor/bootstrap-slider.min.js'
    ],
    'favicon' => 'favicon.ico',
    'helpers' => [
        'factories' => [
            'Zenon\View\Helper\Root\Citation' => 'VuFind\View\Helper\Root\CitationFactory',
            'Zenon\View\Helper\Root\DateTime' => 'VuFind\View\Helper\Root\DateTimeFactory',
            'Zenon\View\Helper\Root\Record' => 'VuFind\View\Helper\Root\RecordFactory',
            'Zenon\View\Helper\Root\RecordLink' => 'Zenon\View\Helper\Root\RecordLinkFactory',
            'Zenon\View\Helper\Root\ResultFeed' => 'VuFind\View\Helper\Root\ResultFeedFactory',
        ],
        'aliases' => [
            'citation' => 'Zenon\View\Helper\Root\Citation',
            'dateTime' => 'Zenon\View\Helper\Root\DateTime',
            'record' => 'Zenon\View\Helper\Root\Record',
            'recordLink' => 'Zenon\View\Helper\Root\RecordLink',
            'resultfeed' => 'Zenon\View\Helper\Root\ResultFeed',
        ],
    ]
];
