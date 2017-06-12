<?php
/**
 * Zend\Feed\Entry extension for Dublin Core
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
 * @package  Feed_Plugins
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Zenon\Feed\Writer\Extension\DublinCore\Renderer;
use VuFind\Feed\Writer\Extension\DublinCore\Renderer\Entry as VuFindEntry;
use DOMDocument, DOMElement;
/**
 * Zend\Feed\Entry extension for Dublin Core
 *
 * Note: There doesn't seem to be a generic base class for this functionality,
 * and creating a class with no parent blows up due to unexpected calls to
 * Itunes-related functionality.  To work around this, we are extending the
 * equivalent Itunes plugin.  This works fine, but perhaps in future there will
 * be a more elegant way to achieve the same effect.
 *
 * @category VuFind
 * @package  Feed_Plugins
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class Entry extends VuFindEntry
{
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
}