<?php
/**
 * Custom record handling for Zenon MARC records.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace Zenon\RecordDriver;
use VuFind\RecordDriver\SolrMarc as VufindSolrMarc;

/**
 * Custom record handling for Zenon MARC records.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrMarc extends VufindSolrMarc
{

	/**
     * Get the thesaurus entries of the record
     *
     * @return array
     */
    public function getThsEntries()
    {

    	$result = array();
    	$fields = $this->marcRecord->getFields('999');

    	foreach ($fields as $currentField) {

    		$entry = [];

            $label = $this->getSubfieldArray($currentField, ['a','r','m','e'], true);
            if (count($label > 0)) $entry['label'] = $label[0];
            else continue;

            $language = $this->getSubfieldArray($currentField, ['9']);
            if (count($language > 0)) $entry['language'] = $language[0];
            else $entry['language'] = 'ger';

            $notation = $this->getSubfieldArray($currentField, ['1']);
            if (count($notation > 0)) $entry['notation'] = $notation[0];
            else continue;

            // TODO: multi language support, until then only show german entries
            if ($entry['language'] == 'ger') $result[] = $entry;

        }

        // only return distinct values
        return array_map('unserialize', array_unique(array_map('serialize', $result)));

    }

    /**
     * Get the host item information (MARC 21 field 773)
     *
     * @return array
     */
    public function getHostItemInformation() {
    	return $this->getFieldArray('773');
    }

    /**
     * Get the parent of the record
     *
     * @return array
     */
    public function getParent()
    {

    	$result = array();
    	$fields = $this->marcRecord->getFields('995');

    	foreach ($fields as $currentField) {
    		$field = $this->getSubfieldArray($currentField, ['a','b','n'], false);
    		if ($field[0] == 'ANA') {
	    		return array(
	    			'id' => $field[1],
	    			'label' => $field[2]
	    		);
	    	}
    	}

        return false;

    }

    /**
     * Get links to iDAI.gazetteer
     *
     * @return array
     */
    public function getGazetteerLinks()
    {

    	$result = array();
    	$thsEntries = $this->getThsEntries();

    	foreach ($thsEntries as $thsEntry) {
    		if (strrpos($thsEntry['notation'], 'zTopog', -strlen($thsEntry['notation'])) !== false) {
    			$result[] = array(
    				'label' => $thsEntry['label'],
    				'uri' => "http://gazetteer.dainst.org/app/#!/search?q=".$thsEntry['notation']
    			);
    		}
    	}

        return $result;

    }

}

?>