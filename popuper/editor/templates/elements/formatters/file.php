<?php

namespace Popuper\Editor\Templates\Elements\Formatters;

/**
 * Class File
 *
 * @package Popuper\Editor\Templates\Elements\Formatters
 */
class File extends Element implements FormatterInterface
{
    /** @var string */
    protected $_address;

    /**
     * @return array
     */
    public function getTemplateData()
    {
        return [$this->_element->address];
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function formatCollectedData($data = null)
    {
        if ($data) {
            return [$this->_element->getDataKey() => $data];
        }

        return null;
    }

}