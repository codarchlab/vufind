<?php

namespace Zenon\Feed\Writer\Extension\DublinCore\Renderer;

use VuFind\Feed\Writer\Extension\DublinCore\Renderer\Entry as ParentEntry;
use DOMDocument, DOMElement;

class Entry extends ParentEntry
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