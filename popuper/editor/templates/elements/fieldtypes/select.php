<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

use Popuper\Helpers\DataKeys;

/**
 * Class Select
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
class Select extends Text implements SelectTypeInterface
{
    /**
     * @return string
     */
    public function getInputType(): string
    {
        return DataKeys::INPUT_TYPE_SELECT;
    }

    /**
     * @return array
     * @throws \Popuper\Editor\Templates\Exceptions\InvalidFieldException
     */
    public function getTemplateData(): array
    {
        return [
            DataKeys::PLACEHOLDER_KEY => $this->_element->description,
            DataKeys::ITEMS_KEY_KEY => SelectableItems::getItemsKey($this->_element)
        ];
    }

    /**
     * @return array
     */
    public function getAvailableValues(): array
    {
        return SelectableItems::getAvailableValues($this->_element);
    }
}