<?php
/**
 * Zend\Feed\Renderer\Entry extension for Dublin Core
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
 * @package  Feed_Plugins
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace VuFind\Feed\Writer\Extension\DublinCore\Renderer;
use DOMDocument, DOMElement,
    Zend\Feed\Writer\Extension\DublinCore\Renderer\Entry as ParentEntry;

/**
 * Zend\Feed\Renderer\Entry extension for Dublin Core
 *
 * @category VuFind2
 * @package  Feed_Plugins
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Entry extends ParentEntry
{
    /**
     * Render entry
     *
     * @return void
     */
    public function render()
    {
        if (strtolower($this->getType()) == 'atom') {
            return;
        }
        $this->setDCFormats($this->dom, $this->base);
        $this->setDCDate($this->dom, $this->base);
        $this->setDCDescriptions($this->dom, $this->base);
        $this->setMediaThumbnail($this->dom, $this->base);
        $this->setContainerReference($this->dom, $this->base);
        parent::render();
    }

    /**
     * Set entry format elements
     *
     * @param DOMDocument $dom  DOM document to update
     * @param DOMElement  $root Root of DOM document
     *
     * @return void
     */
    protected function setDCFormats(DOMDocument $dom, DOMElement $root)
    {
        $dcFormats = $this->getDataContainer()->getDCFormats();
        if (empty($dcFormats)) {
            return;
        }
        foreach ($dcFormats as $data) {
            $format = $this->dom->createElement('dc:format');
            $text = $dom->createTextNode($data);
            $format->appendChild($text);
            $root->appendChild($format);
        }
        $this->called = true;
    }

    /**
     * Set entry date elements
     *
     * @param DOMDocument $dom  DOM document to update
     * @param DOMElement  $root Root of DOM document
     *
     * @return void
     */
    protected function setDCDate(DOMDocument $dom, DOMElement $root)
    {
        $dcDate = $this->getDataContainer()->getDCDate();
        if (empty($dcDate)) {
            return;
        }
        $date = $this->dom->createElement('dc:date');
        $text = $dom->createTextNode($dcDate);
        $date->appendChild($text);
        $root->appendChild($date);
        $this->called = true;
    }

    /**
     * Set entry description elements
     *
     * @param DOMDocument $dom  DOM document to update
     * @param DOMElement  $root Root of DOM document
     *
     * @return void
     */
    protected function setDCDescriptions(DOMDocument $dom, DOMElement $root)
    {
        $dcDescriptions = $this->getDataContainer()->getDCDescriptions();
        if (empty($dcDescriptions)) {
            return;
        }
        foreach ($dcDescriptions as $data) {
            $format = $this->dom->createElement('dc:description');
            $text = $dom->createTextNode($data);
            $format->appendChild($text);
            $root->appendChild($format);
        }
        $this->called = true;
    }

    protected function setContainerReference(DOMDocument $dom, DOMElement $root)
    {
        $reference = $this->getDataContainer()->getContainerReference();
        if(empty($reference)){
            return;
        }

        $format = $this->dom->createElement('dc:relation');
        $text = $dom->createTextNode($reference);
        $format->appendChild($text);
        $root->appendChild($format);

        $this->called = true;
    }

    /**
     * Set entry thumbnail element
     *
     * @param DOMDocument $dom  DOM document to update
     * @param DOMElement  $root Root of DOM document
     *
     * @return void
     */
    protected function setMediaThumbnail(DOMDocument $dom, DOMElement $root)
    {
        $mediaThumbnail = $this->getDataContainer()->getMediaThumbnail();
        if (empty($mediaThumbnail)) {
            return;
        }
        $thumbnail = $this->dom->createElement('media:thumbnail');
        $thumbnail->setAttribute('url', $mediaThumbnail);
        $root->appendChild($thumbnail);
        $this->called = true;
    }
}
