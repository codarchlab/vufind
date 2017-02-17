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
use VuFindCode\ISBN;

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

    const COVERS_DIR = "/usr/local/vufind/local/cache/covers";

    /**
     * Get the full title of the record.
     * Overriden to remove trailing slashes.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->removeTrailingSlash(parent::getTitle());
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     * Overriden to remove trailing slashes.
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->removeTrailingSlash(parent::getShortTitle());
    }

    /**
     * Get a highlighted title string, if available.
     * Overriden to remove trailing slashes.
     *
     * @return string
     */
    public function getHighlightedTitle()
    {
        return $this->removeTrailingSlash(parent::getHighlightedTitle());
    }

    /**
     * Get the text of the part/section portion of the title.
     * Overriden to remove trailing slashes.
     *
     * @return string
     */
    public function getTitleSection()
    {
        return $this->removeTrailingSlash(parent::getTitleSection());
    }

    /**
     * Get the subtitle of the record.
     * Overriden to remove trailing slashes.
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->removeTrailingSlash(parent::getSubtitle());
    }

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

            // return $m as additional search term for Gazetteer
            $searchterm = $this->getSubfieldArray($currentField, ['m']);
            if (count($searchterm > 0)) $entry['searchterm'] = $searchterm[0];

            // return $r as additional search term for Gazetteer
            $searchterm2 = $this->getSubfieldArray($currentField, ['r']);
            if (count($searchterm2 > 0)) $entry['searchterm2'] = $searchterm2[0];

            // TODO: multi language support, until then only show german entries
            if ($entry['language'] == 'ger') $result[] = $entry;

        }

        // only return distinct values
        return array_map('unserialize', array_unique(array_map('serialize', $result)));

    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * Overriden to be able to test if thumbs are available in cache directory.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {
        $arr = parent::getThumbnail($size);
        if (!array_key_exists('isbn', $arr)) return false;
        $isbnObj = new ISBN($arr['isbn']);
        $isbn10 = $isbnObj->get10();
        $isbn13 = $isbnObj->get13();

        if ( file_exists(self::COVERS_DIR . '/medium/' . $isbn10 . '.jpg')
            || file_exists(self::COVERS_DIR . '/medium/' . $isbn13 . '.jpg') )
            return $arr;
        else
            return false;
    }

    /**
     * Get the location (MARC 21 field 852)
     *
     * @return array
     */
    public function getLocation()
    {
    	return $this->getFieldArray('852', array('a','b','e','h'));
    }

    /**
     * Get the basic bibliographic unit (MARC 21 field 866)
     *
     * @return array
     */
    public function getBasicBibliographicUnit()
    {
        return $this->getFieldArray('866');
    }

    /**
     * Get the host item information (MARC 21 field 773)
     *
     * @return array
     */
    public function getHostItemInformation()
    {
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
     * Get parallel records for the record (different editions etc.)
     *
     * @return array
     */
    public function getSeeAlso()
    {

		$result = array();
		$fields = $this->marcRecord->getFields('995');

		foreach ($fields as $currentField) {
			$field = $this->getSubfieldArray($currentField, ['a','b','n'], false);
			if ($field[0] == 'UP') {
				$result[] = array(
					'id' => $field[1],
					'label' => $field[2]
					);
			}
		}

		return $result;

	}

    /**
     * Get parallel records for the record (different editions etc.)
     *
     * @return array
     */
    public function getParallelEditions()
    {

        $result = array();
        $fields = $this->marcRecord->getFields('995');

        foreach ($fields as $currentField) {
            $field = $this->getSubfieldArray($currentField, ['a','b','n'], false);
            if ($field[0] == 'PAR') {
                $result[] = array(
                    'id' => $field[1],
                    'label' => $field[2]
                    );
            }
        }

        return $result;

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
    		if (strrpos($thsEntry['notation'], 'zTopog', -strlen($thsEntry['notation'])) !== false
                    || strrpos($thsEntry['notation'], 'zEuropSüdeuItali', -strlen($thsEntry['notation'])) !== false
                    || strrpos($thsEntry['notation'], 'gazetteer', -strlen($thsEntry['notation'])) !== false) {
    			$result[] = array(
    				'label' => $thsEntry['label'],
    				'uri' => "http://gazetteer.dainst.org/app/#!/search?q=".$thsEntry['notation']
    			);
                 }
                 // use 999 $m (= $thsEntry['searchterm'] as additional
                 // parameter in Gazetteer link, if 999 $1 == 3.00.01.01.*
                 // or 999 $1 == 3.00.01.02.* (in $thsEntry['notation'])
// 15.09.2016 Übernahme nach zenon-branch
/*                 if (strrpos($thsEntry['notation'], '3.00.01.01', -strlen($thsEntry['notation'])) !== false
                    || strrpos($thsEntry['notation'], '3.00.01.02', -strlen($thsEntry['notation'])) !== false) {
                      $result[] = array(
                                'label' => $thsEntry['label'],
                                'uri' => "http://gazetteer.dainst.org/app/#!/search?q=" . $thsEntry['notation'] . ";" . $thsEntry['searchterm']
                        );
                 } */
                 // use 999 $r (= $thsEntry['searchterm2'] as additional
                 // parameter in Gazetteer link, if 999 $1 == xtop* ($thsEntry['notation'])
                 if (strrpos($thsEntry['notation'], 'xtop', -strlen($thsEntry['notation'])) !== false ) {
                      $result[] = array(
                                'label' => $thsEntry['label'],
                                'uri' => "http://gazetteer.dainst.org/app/#!/search?q=" . $thsEntry['notation'] . ";" . $thsEntry['searchterm']
                        );
                  }

    	}

        return $result;

    }

    /**
     * Get Varying Form of Title (MARC field 246)
     *
     * @return array
     */
    public function getVaryingFormOfTitles()
    {
        return $this->getFieldArray('246');
    }

    /**
     * Get additional Title (MARC field 740)
     *
     * @return array
     */
    public function getAdditionalTitles()
    {
        return $this->getFieldArray('740');
    }

    /**
    * Get additional Information (MARC fields 540, 546 & 561)
     *
     * @return array
    */
    public function getAdditionalInformation()
    {
        $fields = ['546', '561'];
        $result = [];
        foreach ($fields as $field) {
            $value = $this->getFieldArray($field);
            if (!empty($value)) $result[] = $value;
        }
        return $result;
    }

     /**
     * Get Additional Physical Form available Note (MARC field 530)
      *
      * @return array
     */
    public function getAdditionalPhysicalFormAvailableNote()
    {
        $result = array();
        $fields = $this->marcRecord->getFields('530');

        foreach ($fields as $currentField) {
            $field = $this->getSubfieldArray($currentField, ['a','u'], false);
            $result[] = array(
              'label' => $field[0],
              'uri' => $field[1]
            );
            
        }

        return $result;
    }

    /**
    * Get Terms Governing Use and Reproduction Note (MARC field 540)
     *
     * @return array
    */
    public function getUsageTerms()
    {
        return $this->getFieldArray('540');
    }

    /**
    * Get Copyright Status (MARC field 542)
     *
     * @return array
    */
    public function getCopyrightStatus()
    {
        return $this->getFieldArray('542',['d']);
    }
    
    private function removeTrailingSlash($s)
    {
        if (strrpos($s, '/') == strlen($s)-1) {
            return substr($s, 0, strrpos($s, '/'));
        } else {
            return $s;
        }
    }

}

?>
