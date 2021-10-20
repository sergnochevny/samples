<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Helpers\DataKeys;

/**
 * Class Text
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
class Text implements FieldTypeInterface
{
    /** @var ElementInterface */
    protected $_element;

    /**
     * Element constructor.
     *
     * @param ElementInterface $element
     */
    public function __construct(ElementInterface $element)
    {
        $this->_element = $element;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function castValue($value)
    {
        return $value;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return DataKeys::INPUT_TYPE_TEXT;
    }

    /**
     * @return array
     */
    public function getTemplateData(): array
    {
        return [];
    }
}