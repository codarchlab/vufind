<?php
/**
 * iDAIGazetter Query Controller
 *
 * PHP version 7
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

class GazetteerSearchController extends VuFindSearchController
{
    protected $searchService = null;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->searchService = $sm->get('VuFindSearch\Service');
        parent::__construct($sm);
    }

    /**
     * Search action
     * 
     * Forwards to VuFind's search interface if bibliographic data associated with the given gazetteer ID 
     * exists.
     *
     * @return mixed
     */
    public function searchAction()
    {
        $gazId =  $this->params()->fromQuery('id');
        if(is_null($gazId)) {
            throw new \VuFind\Exception\BadRequest(
                'No iDAI.gazetteer id provided.'
            );
        }

        $authorityId = AuthoritySearchHelper::getAuthorityId($this->searchService, "iDAI_gazetteer_id", $gazId);
        if (is_null($authorityId)) {
            throw new RecordMissingException(
                'Gazetteer ID:' . $gazId . ' not found.'
            );
        }

        $queryString = urlencode("authority_id_str_mv:" . $authorityId);
        return $this->redirect()->toUrl('/Search/Results?filter[]=~' . $queryString);
    }

    /**
     * Home action
     * 
     * Returns the number of bibliographic records associated with the given gazetteer id.
     *
     * @return mixed
     */
    public function homeAction()
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-type', 'application/json');

        $json = null;

        $gazId =  $this->params()->fromQuery('id');
        if(is_null($gazId)) {
            return $response->setContent(json_encode(array(
                "status" => "error",
                "message" => "No iDAI.gazetteer id provided."
            )));
        }

        $authorityId = AuthoritySearchHelper::getAuthorityId($this->searchService, "iDAI_gazetteer_id", $gazId);

        if (is_null($authorityId)) {
            return $response->setContent(json_encode(array(
                "status" => "ok",
                "found" => 0
            )));
        }

        $biblioSearchResults = AuthoritySearchHelper::getBibliosForAuthorityId($this->searchService, $authorityId);
        $response->setContent(json_encode(array(
            "status" => "ok",
            "found" => $biblioSearchResults->getTotal(),
            "authority_id_str_mv" => $authorityId
        )));
        return $response;
    }
}
