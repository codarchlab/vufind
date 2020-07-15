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
use VuFind\AjaxHandler\PluginManager as AjaxPluginManager;
use Vufind\AjaxHandler\AjaxHandlerInterface;
use VuFind\Search\Results\PluginManager as SearchPluginManager;

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
    protected $search;
    /**
     * Constructor
     *
     * @param AjaxPluginManager $am AJAX Handler Plugin Manager
     * @param SearchPluginManager $sm Search Plugin Manager
     */
    public function __construct(AjaxPluginManager $am, SearchPluginManager $sm)
    {
        parent::__construct($am);
        $this->search = $sm->get('SolrAuth');
    }

    /**
     * List children of a given thesaurus entry
     *
     * @return mixed
     */
    public function childrenAction()
    {
        $this->outputMode = 'json';
        $id = $this->params()->fromQuery('id');

        $params = $this->search->getParams();
        if ($id) {
            $params->setOverrideQuery("ths_parent_id_str:$id");
        } else {
            $params->setOverrideQuery("-ths_parent_id_str:[* TO *] AND ths_id_str:[* TO *]");
        }
        $params->setLimit(10000);
        $params->setSort("ths_heading_str", true);

        $results = $this->search->getResults();
        $json = array();

        foreach ($results as $result) {
            $data = $result->getRawData();
            unset($data['fullrecord']);
            $json[] = $data;
        }

        return $this->getAjaxResponse('application/javascript', $json,200);

    }

}
