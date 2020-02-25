<?php
namespace Zenon\View\Helper\Root;

use DateTime;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use Zend\Feed\Writer\Writer as FeedWriter;
use Zend\Feed\Writer\Feed;
use VuFind\View\Helper\Root\ResultFeed as ParentResultFeed;

class ResultFeed extends ParentResultFeed
{
    public function registerExtensions(\Zend\ServiceManager\ServiceManager $sm)
    {
        $manager = new \Zend\Feed\Writer\ExtensionPluginManager($sm);
        $manager->setInvokableClass(
            'DublinCore\Renderer\Entry',
            'Zenon\Feed\Writer\Extension\DublinCore\Renderer\Entry'
        );
        $manager->setInvokableClass(
            'DublinCore\Entry',
            'Zenon\Feed\Writer\Extension\DublinCore\Entry'
        );
        $manager->setInvokableClass(
            'OpenSearch\Renderer\Feed',
            'VuFind\Feed\Writer\Extension\OpenSearch\Renderer\Feed'
        );
        $manager->setInvokableClass(
            'OpenSearch\Feed',
            'VuFind\Feed\Writer\Extension\OpenSearch\Feed'
        );
        FeedWriter::setExtensionManager($manager);
        FeedWriter::registerExtension('OpenSearch');
    }

    protected function addEntry($feed, $record)
    {
        $entry = $feed->createEntry();
        $title = $record->tryMethod('getTitle');
        $title = empty($title) ? $record->getBreadcrumb() : $title;
        $entry->setTitle(
            empty($title) ? $this->translate('Title not available') : $title
        );
        $serverUrl = $this->getView()->plugin('serverurl');
        $recordLink = $this->getView()->plugin('recordLink');
        try {
            $url = $serverUrl($recordLink->getUrl($record));
        } catch (\Zend\Mvc\Router\Exception\RuntimeException $e) {
            // No route defined? See if we can get a URL out of the driver.
            // Useful for web results, among other things.
            $url = $record->tryMethod('getUrl');
            if (empty($url) || !is_string($url)) {
                throw new \Exception('Cannot find URL for record.');
            }
        }
        $entry->setLink($url);
        $date = $this->getDateModified($record);
        if (!empty($date)) {
            $entry->setDateModified($date);
        }
        $author = $record->tryMethod('getPrimaryAuthor');
        if (!empty($author)) {
            $entry->addAuthor(['name' => $author]);
        }
        $formats = $record->tryMethod('getFormats');
        if (is_array($formats)) {
            foreach ($formats as $format) {
                $entry->addDCFormat($format);
            }
        }
        $dcDate = $this->getDcDate($record);
        if (!empty($dcDate)) {
            $entry->setDCDate($dcDate);
        }
        $dcDescriptions = $record->tryMethod('getSummary');
        if (!empty($dcDescriptions)) {
            foreach ($dcDescriptions as $dcDescription) {
                $entry->addDCDescription($dcDescription);
            }
        }

        $containerReference = $record->tryMethod('getContainerReference');
        if(!empty($containerReference)) {
            $entry->setContainerReference($containerReference);
        }

        $thumb = $record->getThumbnail('medium');
        $urlHelper = $this->getView()->plugin('url');
        if (!empty($thumb)) {
            $mediaThumbnail = $serverUrl($urlHelper('cover-show') . '?' . http_build_query($thumb));
            if (!empty($mediaThumbnail)) {
                $entry->setMediaThumbnail($mediaThumbnail);
            }
        }

        $feed->addEntry($entry);
    }
}
