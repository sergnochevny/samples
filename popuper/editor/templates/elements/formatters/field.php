<?php

namespace Popuper\Editor\Templates\Elements\Formatters;

use Arr;
use Popuper\Editor\Templates\Elements\FieldTypes\FieldTypeInterface;
use Popuper\Helpers\PrepareData as PrepareDataHelper;
use Popuper\Repositories\ObjectItems\SavingItem;

/**
 * Class Field
 *
 * @package Popuper\Editor\Templates\Elements\Formatters
 */
class Field extends Element implements FormatterInterface
{
    /**
     * @return array
     */
    public function getTemplateData()
    {
        $templateData = parent::getTemplateData();

        /**
         * @var \Popuper\Editor\Templates\Elements\Field $element
         */
        $element = $this->_element;

        $fieldType = $element->getFieldType();
        if ($fieldType instanceof FieldTypeInterface) {
            $templateData = Arr::mergeRecursive(
                $templateData,
                $fieldType->getTemplateData()
            );
        }


        return $templateData;
    }

    /**
     * @param array $data
     *
     * @return null|SavingItem[]
     */
    public function getSavingData(array $data = [])
    {
        $values = $this->_getElementValuesFromRawData($data);
        $values = ($values && is_array($values)) ? $values : [];

        if ($values) {
            $savingElements = [];
            foreach ($values as $langIso => $value) {
                if ($landId = PrepareDataHelper::getIdLangByIso($langIso)) {
                    $savingElements[] = new SavingItem(
                        $this->_prepareItemData($landId, $value)
                    );
                }
            }

            return $savingElements;
        }

        return null;
    }

    /**
     * @param $landId
     * @param $value
     *
     * @return array
     */
    protected function _prepareItemData($landId, $value): array
    {
        /**
         * @var \Popuper\Editor\Templates\Elements\Field $element
         */
        $element = $this->_element;

        $fieldType = $element->getFieldType();
        if ($fieldType instanceof FieldTypeInterface) {
            $value = $fieldType->castValue($value);
        }

        return [
            SavingItem::TEMPLATE_ID => $element->id,
            SavingItem::LANGUAGE => $landId,
            SavingItem::VALUE => $value,
        ];
    }

}