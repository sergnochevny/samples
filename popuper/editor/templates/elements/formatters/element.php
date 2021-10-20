<?php

namespace Popuper\Editor\Templates\Elements\Formatters;

use Arr;
use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Helpers\PrepareData as PrepareDataHelper;
use Popuper\Repositories\ObjectItems\SavingItem;

/**
 * Class Element
 *
 * @package Popuper\Editor\Templates\Elements\Formatters
 */
class Element implements FormatterInterface
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
     * @return array
     */
    public function getTemplateData()
    {
        return array_filter($this->_element->toArray());
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function formatCollectedData($data = null)
    {
        if ($data) {
            return [$this->_element->getDataKey() => [$this->_element->id => $data]];
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return null|SavingItem[]
     */
    public function getSavingData(array $data = [])
    {
        $value = $this->_getElementValuesFromRawData($data);

        if ($value) {
            return [
                new SavingItem(
                    [SavingItem::TEMPLATE_ID => $value]
                ),
            ];
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return mixed
     *
     */
    protected function _getElementValuesFromRawData(array $data)
    {
        $valuesPool = PrepareDataHelper::extractValidatedValues($data);
        $valuesPool = ($valuesPool && is_array($valuesPool)) ? $valuesPool : [];

        return Arr::path($valuesPool, $this->_element->id);
    }

}