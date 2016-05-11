<?php
/**
 * Holdings (ILS) tab
 *
 * PHP version 5
 *
 * Copyright (C) Deutsches Archäologisches Institut 2016.
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
 * @category Zenon
 * @package  RecordTabs
 * @author   Sebastian Cuy <sebastian.cuy@dainst.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_tabs Wiki
 */
namespace Zenon\RecordTab;
use VuFind\RecordTab\AbstractBase;

/**
 * Holdings (ILS) tab
 *
 * @category Zenon
 * @package  RecordTabs
 * @author   Sebastian Cuy <sebastian.cuy@dainst.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_tabs Wiki
 */
class Access extends AbstractBase
{
    /**
     * ILS connection (or false if not applicable)
     *
     * @param \VuFind\ILS\Connection|bool
     */
    protected $catalog;

    /**
     * Constructor
     *
     * @param \VuFind\ILS\Connection|bool $catalog ILS connection to use to check
     * for holdings before displaying the tab; set to false if no check is needed
     */
    public function __construct($catalog)
    {
        $this->catalog = ($catalog && $catalog instanceof \VuFind\ILS\Connection)
            ? $catalog : false;
    }

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Access';
    }

    /**
     * Support method used by template -- extract all unique call numbers from
     * an array of items.
     *
     * @param array $items Items to search through.
     *
     * @return array
     */
    public function getUniqueCallNumbers($items)
    {
        $callNos = array();
        foreach ($items as $item) {
            if (isset($item['callnumber']) && strlen($item['callnumber']) > 0) {
                $callNos[] = $item['callnumber'];
            }
        }
        sort($callNos);
        return array_unique($callNos);
    }

    /**
     * Is this tab active?
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }
}