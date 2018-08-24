<?php
/**
 * Factory for Zenon view helpers.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2014.
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
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Zenon\View\Helper\Root;
use Zend\ServiceManager\ServiceManager;
use VuFind\View\Helper\Root\Factory as ParentFactory;

/**
 * Factory for Zenon view helpers.
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 *
 * @codeCoverageIgnore
 */
class Factory extends ParentFactory
{
    /**
     * Construct the Citation helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Citation
     */
    public static function getCitation(ServiceManager $sm)
    {
        return new Citation($sm->getServiceLocator()->get('VuFind\DateConverter'));
    }

    /**
     * Construct the DateTime helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return DateTime
     */
    public static function getDateTime(ServiceManager $sm)
    {
        return new DateTime($sm->getServiceLocator()->get('VuFind\DateConverter'));
    }

    /**
     * Construct the RecordLink helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RecordLink
     */
    public static function getRecordLink(ServiceManager $sm)
    {
        $recordLink = new RecordLink(
            $sm->getServiceLocator()->get('VuFind\RecordRouter'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('zenon-config')
        );
        $recordLink->attachSearchService($sm->getServiceLocator()->get('VuFind\Search'));
        return $recordLink;
    }

    /**
     * Construct the Record helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Record
     */
    public static function getRecord(ServiceManager $sm)
    {
        $helper = new Record(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config')
        );
        $helper->setCoverRouter(
            $sm->getServiceLocator()->get('VuFind\Cover\Router')
        );
        return $helper;
    }
}
