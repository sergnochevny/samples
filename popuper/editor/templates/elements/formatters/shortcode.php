<?php

namespace Popuper\Editor\Templates\Elements\Formatters;

use Popuper\Repositories\ObjectItems\SavingItem;

/**
 * Class ShortCode
 *
 * @package Popuper\Editor\Templates\Elements\Formatters
 */
class ShortCode extends Element implements FormatterInterface
{
    /**
     * @param array $data
     *
     * @return null|SavingItem[]
     */
    public function getSavingData(array $data = [])
    {
        $value = $this->_getElementValuesFromRawData($data);

        if ($value == $this->_element->id) {
            return [
                new SavingItem(
                    [
                        SavingItem::TEMPLATE_ID => $this->_element->id,
                    ]
                ),
            ];
        }

        return null;
    }

}