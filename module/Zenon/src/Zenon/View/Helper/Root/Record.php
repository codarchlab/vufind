<?php
/**
 * Record driver view helper
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
 * @category Zenon
 * @package  View_Helpers
 * @author   Sven Wolter <sven.wolter@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Zenon\View\Helper\Root;

use VuFind\View\Helper\Root\Record as ParentRecord;

/**
 * Record driver view helper for Zenon Module
 *
 * @category Zenon
 * @package  View_Helpers
 * @author   Sven Wolter <sven.wolter@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class Record extends ParentRecord
{
    /**
     * Store a record driver object and return this object so that the appropriate
     * template can be rendered.
     *
     * @param \VuFind\RecordDriver\AbstractBase $driver Record driver object.
     *
     * @return Record
     */
    public function __invoke($driver)
    {
        // Set up context helper:
        $contextHelper = $this->getView()->plugin('context');
        $this->contextHelper = $contextHelper($this->getView());

        // Gets invoked with VuFind\RecordDriver\SolrMarc

        // Set up driver context:
        $this->driver = $driver;
        return $this;
    }

    /**
     * Get HTML to render a title.
     *
     * @param int $maxLength Maximum length of non-highlighted title.
     *
     * @return string
     */
    public function getTitleHtml($maxLength = 180)
    {
        $highlightedTitle = $this->driver->tryMethod('getHighlightedTitle');
        $title = trim($this->driver->tryMethod('getTitle'));
        $titleSection = $this->driver->tryMethod('getTitleSection');

        if (!empty($highlightedTitle)) {
            if(!empty($titleSection)) {
                $highlightedTitle = $highlightedTitle . " " . $titleSection;
            }
            $highlight = $this->getView()->plugin('highlight');
            $addEllipsis = $this->getView()->plugin('addEllipsis');
            return $highlight($addEllipsis($highlightedTitle, $title));
        }
        if (!empty($title)) {
            if(!empty($titleSection)) {
                $title = $title . " " . $titleSection;
            }
            $escapeHtml = $this->getView()->plugin('escapeHtml');
            $truncate = $this->getView()->plugin('truncate');
            return $escapeHtml($truncate($title, $maxLength));
        }
        $transEsc = $this->getView()->plugin('transEsc');
        return $transEsc('Title not available');
    }

}
