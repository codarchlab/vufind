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
use Zend\Config\Reader\Json as configJson;


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
     * Zenon configuration
     *
     * @var \Zend\Config\Config
     */
    protected $zenonConfig;

    public function __construct($mainConfig = null, $recordConfig = null,
                                $searchSettings = null, $zenonConfig = null
    ) {
        $this->zenonConfig = $zenonConfig;
        parent::__construct($mainConfig, $recordConfig, $searchSettings);
    }
    /**
     * Get the title of the record.
     * Overridden to adapt GBV solr schema divergency and to remove trailing slashes.
     *
     * @return string
     */
    public function getTitle()
    {
        $title = parent::getTitle();
        if(is_array($title)) {
            $title = $title[0];
        }
        return $this->removeTrailingSlash($title);
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     * Overridden to adapt GBV solr schema divergency and to remove trailing slashes.
     *
     * @return string
     */
    public function getShortTitle()
    {
        $shortTitle = parent::getShortTitle();
        if(is_array($shortTitle)) {
            $shortTitle = $shortTitle[0];
        }
        return $this->removeTrailingSlash($shortTitle);
    }

    /**
     * Get a highlighted title string, if available.
     * Overridden to adapt GBV solr schema divergency and to remove trailing slashes.
     *
     * @return string
     */
    public function getHighlightedTitle()
    {
        $highlightedTitle = parent::getHighlightedTitle();
        if(is_array($highlightedTitle)) {
            $highlightedTitle = $highlightedTitle[0];
        }
        return $this->removeTrailingSlash($highlightedTitle);
    }

    /**
     * Get the text of the part/section portion of the title.
     * Overridden to adapt GBV solr schema divergency and to remove trailing slashes.
     *
     * @return string
     */
    public function getTitleSection()
    {
        $titleSection = parent::getTitleSection();
        if(is_array($titleSection)) {
            $titleSection = $titleSection[0];
        }
        return $this->removeTrailingSlash($titleSection);
    }

    /**
     * Get the subtitle of the record.
     * Overridden to adapt GBV solr schema divergency and to remove trailing slashes.
     *
     * @return string
     */
    public function getSubtitle()
    {
        $subTitle = parent::getSubtitle();
        if(is_array($subTitle)) {
            $subTitle = $subTitle[0];
        }
        return $this->removeTrailingSlash($subTitle);
    }

	/**
     * Get the thesaurus entries of the record
     *
     * @return array
     */
    public function getThsEntries()
    {

    	$result = array();
    	$fields = $this->getMarcRecord()->getFields('999');

    	foreach ($fields as $currentField) {

    		$entry = [];

    		// ND201132015 = Non-descriptor flag
            $ignore = $this->getSubfieldArray($currentField, ['g'], false);
            if (!empty($ignore) && $ignore[0] == 'ND201132015') continue;

            $label = $this->getSubfieldArray($currentField, ['a','r','m','e'], true);

            if (count($label) > 0) $entry['label'] = $label[0];
            else continue;

            $language = $this->getSubfieldArray($currentField, ['9']);
            if (count($language) > 0) $entry['language'] = $language[0];
            else $entry['language'] = 'ger';

            $notation = $this->getSubfieldArray($currentField, ['1']);
            if (count($notation) > 0) $entry['notation'] = $notation[0];
            else continue;

            // return $m as additional search term for Gazetteer
            $searchterm = $this->getSubfieldArray($currentField, ['m']);
            if (!empty($searchterm)) $entry['searchterm'] = $searchterm[0];

            // return $r as additional search term for Gazetteer
            $searchterm2 = $this->getSubfieldArray($currentField, ['r']);
            if (!empty($searchterm2)) $entry['searchterm2'] = $searchterm2[0];

            // yes, ugly.
            $belgianLocationLabel = $this->getSubfieldArray($currentField, ['r']);
            if(!empty($belgianLocationLabel))
                $entry['belgianLocationLabel'] = $belgianLocationLabel[0];

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
     * Get the host item information (MARC 21 field 773), also retrieves custom, and deprecated HostItemInformation
     * in field 995 (ZENON data).
     *
     * @return array
     */
    public function getHostItemInformation()
    {
        $results = [];

        $customFieldData = $this->getCustomFieldHostItemLinkData();
        if($customFieldData) {
            $results = $customFieldData;
        }

        $fields =  $this->getMarcRecord()->getFields('773');
        foreach($fields as $currentField) {
            $recordControlNumber = $currentField->getSubfield('w');
            if($recordControlNumber) {
                $data = $this->getHostItemLinkData($currentField);
                if($data) array_push($results, $data);
            }
            else {
                $textEntry = $this->getSubfieldArray($currentField, ['a', 'b', 't', 'g', 'n', 'x'], false);
                if(sizeOf($textEntry) > 0) {
                    array_push($results, array('id' => null, 'label' => join(', ', $textEntry)));
                }
            }
        }
        return $results;
    }

    private function getHostItemLinkData($currentField)
    {
        $recordControlNumber = $currentField->getSubfield('w');

        if($recordControlNumber) {
            $recordControlNumber = $recordControlNumber->getData();
        }
        else{
            $recordControlNumber = false;
        }

        // Pattern matches GBV notation, example id: NLEJ102577161, field value 773w: "(DE-601)NLEJ000028940"
//        preg_match('/^\(.*\)(.*)$/', $currentField->getSubfield('w')->getData(), $match);
//        if(sizeof($match) == 2){
//            $recordControlNumber = $match[1];
//        }

        $textEntry = $this->getSubfieldArray($currentField, ['a', 'b', 't', 'g', 'n', 'x'], false);
        if(sizeOf($textEntry) > 0) {
            return array('id' => $recordControlNumber, 'label' => join(', ', $textEntry));
        }
        else {
            return null;
        }
    }

    private function getCustomFieldHostItemLinkData()
    {
    	$linkType = 'ANA';

        return $this->createCustomFieldLinkArray($linkType);
    }

    /**
     * Get the item's publication information, if no date is found in 260c/264c, get manufacturing date out of 260g/264g
     * if there is also no date information found in 260g/264g, parse control field 008 for a date match
     *
     * @param string $subfield The subfield to retrieve ('a' = location, 'c' = publish date)
     *
     * @return array
     */
    protected function getPublicationInfo($subfield = 'a')
    {
        $results = parent::getPublicationInfo($subfield);

        if (empty($results) && $subfield == 'c') {
            $results = parent::getPublicationInfo('g');

            if (empty($results)) {
                $generalInformation = $this->getMarcRecord()->getField('008');

                if ($generalInformation) {
                    preg_match('/^.{7}(\d{4}).*$/', $generalInformation->getData(), $match);
                    if ($match && sizeof($match) == 2) {
                        array_push($results, $match[1]);
                    }
                }
            }
        }

        return $results;
    }


    /**
     * Get parallel records for the record (different editions etc.)
     *
     * @return array
     */
    public function getSeeAlso()
    {
        $linkType = 'UP';

        return $this->createCustomFieldLinkArray($linkType);
	}

    /**
     * Get parallel records for the record (different editions etc.)
     *
     * @return array
     */
    public function getParallelEditions()
    {
        $linkType = 'PAR';

        return $this->createCustomFieldLinkArray($linkType);
    }


    private function createGazetteerQueryString($thsEntry){
        $query = $thsEntry['notation'];
        if(isset($thsEntry['searchterm'])) {
            $query = $query . ";" . $thsEntry['searchterm'];
        }
        if(isset($thsEntry['searchterm2'])) {
            $query = $query  . ";" . $thsEntry['searchterm2'];
        }
        return $query;
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
            $notation = strtolower($thsEntry['notation']);
            $query = $this->createGazetteerQueryString($thsEntry);
            if(strrpos($notation, 'xtoplandbelgort', -strlen($notation)) !== false) {
                $result[] = array(
                    'label' => $thsEntry['belgianLocationLabel'],
                    'uri' => "http://gazetteer.dainst.org/app/#!/search?q=".$query
                );
            }
            if (strrpos($notation, 'ztopog', -strlen($notation)) !== false
                || strrpos($notation, 'zeuropsüdeuitali', -strlen($notation)) !== false
                || strrpos($notation, 'gazetteer', -strlen($notation)) !== false
                || strrpos($notation, 'xtop', -strlen($notation)) !== false) {
                $result[] = array(
                    'label' => $thsEntry['label'],
                    'uri' => "http://gazetteer.dainst.org/app/#!/search?q=".$query
                );
            }
    	}

        return $result;

    }

    /**
     * Get Marc control number.
     */
    public function getControlNumber() {
        if(!$this->getMarcRecord()->getField('001')->toRaw())
            return null;

        return trim($this->getMarcRecord()->getField('001')->toRaw(), "\x00..\x1F");
    }

    /**
     * Get Link (if exists) to iDAI.publications.
     */
    public function getPublicationsLink() {

        $result = array();

        $content = file_get_contents('./local/iDAI.world/publications_mapping.json');

        if($content != null){
            $controlNumber = $this->getControlNumber();
            $reader = new configJson();
            $data = $reader->fromString($content);

            if (array_key_exists($controlNumber, $data))
                array_push($result, $data[$controlNumber]);
        }

        $content_static = file_get_contents('./local/iDAI.world/publications_mapping_static.json');
        if($content_static != null){
            $controlNumber = $this->getControlNumber();
            $reader = new configJson();
            $data = $reader->fromString($content_static);

            if (array_key_exists($controlNumber, $data))
                array_push($result, $data[$controlNumber]);
        }

        return $result;
    }

    /**
     * Get Link (if exists) to CHRE.
     */
    public function getCHRELink() {
        $fileContent = file('./local/iDAI.world/chre_mapping.csv');
        if($fileContent == null){
            return false;
        }

        $csvData = array_map('str_getcsv', $fileContent);
        $controlNumber = $this->getControlNumber();
        foreach ($csvData as $csvLines => $csvLine) {
            if ($csvLine[0] == $controlNumber) {
                return "http://chre.ashmus.ox.ac.uk/reference/" . $csvLine[1];
            }
        }

        return false;
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
        $fields = $this->getMarcRecord()->getFields('530');

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
     * Try parsing the page range for an artical from the physical description filed (300a).
     *
     * @return string
     */
    public function getPageRangeFromPhysicalDescription() {

        $value = $this->getFieldArray('300',['a']);
        if(!empty($value)){
            preg_match('/((\d+-\d+)|(\d+))/', $value[0], $matches);
            if(!empty($matches)){
                return $matches[1];
            } else {
                return '';
            }
        } else {
            return '';
        }
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

    /**
     * Creates an array of link information from custom field 995 and subfields 'a', 'b', and 'n'
     *
     * @param $linkType
     * @return array
     */
    private function createCustomFieldLinkArray($linkType)
    {
        $result = [];
        $fields = $this->getMarcRecord()->getFields('995');

        foreach ($fields as $currentField) {
            $currentLinkType = $currentField->getSubfield('a')->getData();

            if($linkType == $currentLinkType) {

                $zenonId = $this->zenonConfig->Records->localRecordPrefix . $currentField->getSubfield('b')->getData();
                $label = $currentField->getSubfield('n')->getData();
                $link = [
                    'id' => $zenonId,
                    'label' => $label,
                ];
                array_push($result, $link);
            }
        }

        return $result;
    }

    /**
     * Get an array of strings representing citation formats supported
     * by this record's data (empty if none).  For possible legal values,
     * see /application/themes/root/helpers/Citation.php, getCitation()
     * method.
     *
     * @return array Strings representing citation formats.
     */
    protected function getSupportedCitationFormats()
    {
        return ['DAI', 'APA', 'Chicago', 'MLA'];
    }
}

?>
