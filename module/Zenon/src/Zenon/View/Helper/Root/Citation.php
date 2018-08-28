<?php
/**
 * Citation view helper
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Zenon\View\Helper\Root;
use VuFind\View\Helper\Root\Citation as VufindCitation;

/**
 * Citation view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class Citation extends VufindCitation
{
    /**
     * Get DAI citation.
     *
     * This function assigns all the necessary variables and then returns an DAI
     * citation.
     *
     * @return string
     */
    public function getCitationDAI()
    {
        $series = $this->driver->tryMethod('getSeries');

        if (!empty($series)){
            $this->details['series'] = $series;
        }

        $dai = [
            'title' => $this->getDAITitle(),
            'authors' => $this->getDAIAuthors(),
            'edition' => $this->getEdition(),
            'series' => $this->getDAISeries()
        ];

        if (!empty($this->details['pubPlace'])){
            $dai['pubPlace'] = $this->stripPunctuation($this->details['pubPlace']) . ' ' .  $this->details['pubDate'];
        }

        $dai['hostItemInformation'] = $this->driver->tryMethod('getHostItemInformation');
        if (!empty($dai['hostItemInformation'])){
            $dai['hostItemInformation'] = $dai['hostItemInformation'][0]['label'];
        }

        $titleSection = $this->driver->tryMethod('getTitleSection');
        if (!empty($titleSection)) {
            $dai['titleSection'] = $titleSection;
        }

        // Behave differently for books vs. journals:
        $partial = $this->getView()->plugin('partial');
        if (empty($this->details['journal'])) {
            $dai['publisher'] = $this->getPublisher();
            $dai['year'] = $this->getYear();
            return $partial('Citation/dai.phtml', $dai);
        } else {
            list($dai['volume'], $dai['issue'], $dai['date'])
                = $this->getAPANumbersAndDate();
            $dai['journal'] = $this->details['journal'];
            $dai['pageRange'] = $this->getPageRange();

            if (empty($dai['pageRange'])){
                $dai['pageRange'] = $this->stripPunctuation(
                    $this->driver->tryMethod('getPageRangeFromPhysicalDescription')
                );
            }

            if ($doi = $this->driver->tryMethod('getCleanDOI')) {
                $dai['doi'] = $doi;
            }
            return $partial('Citation/dai-article.phtml', $dai);
        }
    }

    /**
     * Get the full title for an DAI citation.
     *
     * @return string
     */
    protected function getDAITitle()
    {
        // Create Title
        $title = $this->stripPunctuation($this->details['title']);
        if (isset($this->details['subtitle'])) {
            $subtitle = $this->stripPunctuation($this->details['subtitle']);
            // Capitalize subtitle and apply it, assuming it really exists:
            if (!empty($subtitle)) {
                $subtitle
                    = strtoupper(substr($subtitle, 0, 1)) . substr($subtitle, 1);
                $title .= '. ' . $subtitle;
            }
        }

        return $title;
    }

    /**
     * Get an array of authors for an DAI citation.
     *
     * @return string
     */
    public function getDAIAuthors(){
        $authorStr = '';
        if (isset($this->details['authors']) && is_array($this->details['authors'])) {
            $i = 0;
            foreach ($this->details['authors'] as $author) {
                $author = $this->abbreviateName($author);
                $author = $this->reverseName($author);

                if (($i + 1 == count($this->details['authors'])) && $i > 0) {
                    $authorStr .= $this->stripPunctuation($author) . ',';
                } elseif (count($this->details['authors']) > 1) {
                    $authorStr .= $author . ' – ';
                } else {
                    $authorStr .= $this->stripPunctuation($author) . ',';
                }
                $i++;
            }
        }

        return (empty($authorStr) ? '' : $authorStr);
    }

    /**
     * Get the series description for an DAI citation.
     *
     * @return string
     */
    protected function getDAISeries()
    {
        $seriesStr = '';

        if (isset($this->details['series'])) {
            $i = 0;
            foreach($this->details['series'] as $series){
                if($i != 0){
                    $seriesStr = $seriesStr . ' = ';
                }
                $seriesStr = $seriesStr . $this->stripPunctuation($series['name']) . ' ' . $series['number'];
                $i++;
            }
        }

        return $seriesStr;
    }

}
