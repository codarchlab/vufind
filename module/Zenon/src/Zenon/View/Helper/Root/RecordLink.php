<?php
/**
 * Record link view helper
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category Zenon
 * @package  View_Helpers
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Zenon\View\Helper\Root;
use VuFind\View\Helper\Root\RecordLink as ParentRecordLink;
/**
 * Record link view helper for Zenon Module
 *
 * @category Zenon
 * @package  View_Helpers
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class RecordLink extends ParentRecordLink
{
    protected $searchService = null;

    /**
     * Zenon configuration
     *
     * @var \Zend\Config\Config
     */
    protected $zenonConfig;

    /**
     * Constructor
     *
     * @param \VuFind\Record\Router $router Record router
     */
    public function __construct(\VuFind\Record\Router $router, $zenonConfig = null, $searchService = null)
    {
        $this->zenonConfig = $zenonConfig;
        $this->searchService = $searchService;
        parent::__construct($router);
    }
    /**
     * Attach a Search Results Plugin Manager connection and related logic to
     * the driver
     *
     * @param \VuFindSearch\Service $service Search Service Manager
     *
     * @return void
     */
    public function attachSearchService(\VuFindSearch\Service $service)
    {
        $this->searchService = $service;
    }

    /**
     * Given a record driver, generate a URL to fetch all child records for it.
     *
     * @param \VuFind\RecordDriver\AbstractBase $driver Host Record.
     *
     * @return string
     */
    public function getChildRecordSearchUrl($driver)
    {
        $urlHelper = $this->getView()->plugin('url');
        $url = $urlHelper('search-results')
            . '?lookfor='
            . urlencode(addcslashes($driver->getRawData()['is_hierarchy_id'], '"'))
            . '&type=ParentID';
// Make sure everything is properly HTML encoded:
        $escaper = $this->getView()->plugin('escapehtml');
        return $escaper($url);
    }

    public function getMovedZenonRecordId($recordPath) {
        $match = null;
        if(preg_match('/.*\/Record\/(\d{9}).*/', $recordPath, $matches)){
            $id = $this->zenonConfig->Records->localRecordPrefix . $matches[1];
            $query = new \VuFindSearch\Query\Query(
                'id:"' . $id . '"'
            );
            // Disable highlighting for efficiency; not needed here:
            $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);

            if($this->searchService->search('Solr', $query, 0, 0, $params)->getTotal() == 1) {
                return $id;
            }
        }
        return false;
    }

    public function aggregateHostItemHoldings($zenonId, $currentDepth = 0) {
        
        if($currentDepth > 5) {
            return [];
        }

        $query = new \VufindSearch\Query\Query(
            'id:"' . $zenonId . '"'
        );
        // Disable highlighting for efficiency; not needed here:
        $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);

        $result = $this->searchService->retrieve('Solr', $zenonId)->first();
        if($result) {
            $holdings = $result->getRealTimeHoldings();

            // for parent in 773
            $hostItem = $result->getHostItemInformation();

            if($hostItem && $hostItem['id']) {
                $holdings = ['holdings' =>  
                    array_merge(
                        $holdings['holdings'], 
                        $this->aggregateHostItemHoldings($hostItem['id'], $currentDepth + 1)['holdings'])
                    ];
            }
        } else {
            $holdings = ['holdings' => []];
        }

        return $holdings;
    }
}