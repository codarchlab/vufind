<?php
/**
 * Zenon Thesaurus controller
 *
 * PHP version 5
 *
 * Copyright (C) Deutsches Archäologisches Institut 2015.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Controller
 * @author   Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace Zenon\Controller;

use VuFind\Controller\AjaxController as AjaxController;

/**
 * Return thesaurus entries from the index
 *
 * @category VuFind2
 * @package  Controller
 * @author   Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class ThesaurusController extends AjaxController
{

    /**
     * List children of a given thesaurus entry
     *
     * @return mixed
     */
    public function childrenAction()
    {

        $this->outputMode = 'json';

        $id = $this->params()->fromQuery('id');

        $search = $this->getServiceLocator()
            ->get('VuFind\SearchResultsPluginManager')->get('SolrAuth');

        $params = $search->getParams();
        if ($id) {
            $params->setOverrideQuery("parent_id_str:$id");
        } else {
            $params->setOverrideQuery("-parent_id_str:[* TO *]");
        }
        $params->setLimit(10000);
        $params->setSort("heading", true);

        $results = $search->getResults();

        $json = array();
        foreach ($results as $result) {
            $json[] = $result->getJSON();
        }

        return $this->output($json, parent::STATUS_OK);
        
    }

}
