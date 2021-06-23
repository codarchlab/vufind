<?php
/**
 * General Authority Search Query Controller
 *
 * PHP version 7
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
 * @package  Controller
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Zenon\Controller;

use Zend\ServiceManager\ServiceLocatorInterface;
use VuFind\Controller\SearchController as VuFindSearchController;
use VuFind\Exception\RecordMissing as RecordMissingException;

use Zenon\Controller\AuthoritySearchHelper;

class AuthoritySearchController extends VuFindSearchController
{
    protected $searchService = null;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->searchService = $sm->get('VuFindSearch\Service');
        parent::__construct($sm);
    }

    /**
     * Home action
     * 
     * Returns the the searched authorities
     *
     * @return mixed
     */
    public function homeAction()
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-type', 'application/json');

        $json = null;

        $q = $this->params()->fromQuery('q');
        if(is_null($q)) {
            throw new \VuFind\Exception\BadRequest(
                "No query parameter 'q' provided."
            );
        }

        $limit = $this->params()->fromQuery('limit');
        if(is_null($limit)) {
            $limit = 100;
        } else {
            if(is_numeric($limit)) {
                $limit = (int) $limit;
            } else {
                throw new \VuFind\Exception\BadRequest(
                    "Invalid parameter 'limit' provided."
                );
            }
        }

        $offset = (int) $this->params()->fromQuery('offset');
        if(is_null($offset)) {
            $offset = 0;
        } else {
            if(is_numeric($offset)) {
                $offset = (int) $offset;
            } else {
                throw new \VuFind\Exception\BadRequest(
                    "Invalid parameter 'offset' provided."
                );
            }   
        }

        $authoritySearchResult = AuthoritySearchHelper::searchAuthorities($this->searchService, $q, $limit, $offset);

        $response_records = $this->search_result_to_response_json($authoritySearchResult->getRecords());

        #print_r($authoritySearchResult->getTotal());
        return $response->setContent(json_encode(array(
            "status" => "ok",
            "records" => $response_records,
            "found" => $authoritySearchResult->getTotal()
        )));
    }
    
    private function search_result_to_response_json($searchResultRecords) {
        $finalRecords = array();

        foreach ($searchResultRecords as $searchRecord) {
            $raw = $searchRecord->getRawData();

            unset($raw['fullrecord']);
            unset($raw['_version_']);
            unset($raw['ths_label_eng_str']);
            unset($raw['ths_label_fre_str']);
            unset($raw['ths_label_ita_str']);
            unset($raw['ths_label_spa_str']);
            unset($raw['ths_label_ger_str']);
            unset($raw['ths_label_pol_str']);
            unset($raw['ths_label_gre_str']);
            
            array_push($finalRecords, $raw);
        }

        return $finalRecords;
    }
}
