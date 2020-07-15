<?php
/**
 * Book Bag / Bulk Action Controller
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
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Zenon\Controller;
use http\QueryString;
use VuFind\Controller\SearchController as VuFindSearchController;

use VuFind\Exception\RecordMissing as RecordMissingException;
use VuFindSearch\Query\Query;
use Zend\ServiceManager\ServiceLocatorInterface;
/**
 * Gazetteer Link Controller
 *
 * @category VuFind
 * @package  Controller
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class GazetteerSearchController extends VuFindSearchController
{
    /**
     * Home action
     *
     * @return mixed
     */

    protected $authoritySearch = null;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->authoritySearch = $sm->get('VuFindSearch\Service');
        parent::__construct($sm);
    }

    public function homeAction()
    {
        $gazId =  $this->params()->fromQuery('id');
        if(is_null($gazId)) {
            throw new \VuFind\Exception\BadRequest(
                'No iDAI.gazetteer "id" provided.'
            );
        }

        $query = new Query('iDAI_gazetteer_id:' . $this->escapeForSolr($gazId));
        $authoritySearchResults = $this->authoritySearch->search('SolrAuth', $query)->first();
        if (is_null($authoritySearchResults)) {
            throw new RecordMissingException(
                'Thesauri ID:' . $gazId . ' does not exist.'
            );
        }
        $authorityId = $authoritySearchResults->getRawData()['id'];

        $queryString = urlencode("authority_id_str_mv:" . $authorityId);
        return $this->redirect()->toUrl('/Search/Results?filter[]=~' . $queryString);
    }

    /**
     * Escape a string for inclusion in a Solr query.
     *
     * @param string $str String to escape
     *
     * @return string
     */
    protected function escapeForSolr($str)
    {
        return '"' . addcslashes($str, '"') . '"';
    }
}
