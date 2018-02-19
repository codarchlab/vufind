<?php

namespace Zenon\Feed\Writer\Extension\DublinCore;
use VuFind\Feed\Writer\Extension\DublinCore\Entry as ParentEntry;

class Entry extends ParentEntry{

    /**
     * References
     * @var array
     */
    protected $containerReference = null;

    /**
     * Add container references.
     *
     * @param string $references References to add.
     *
     * @return void
     */
    public function setContainerReference($containerReference)
    {
        $this->containerReference = $containerReference;
    }

    /**
     * Get the container references.
     *
     * @return array
     */
    public function getContainerReference()
    {
        return $this->containerReference;
    }
}