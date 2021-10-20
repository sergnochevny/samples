<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Editor\Templates\Exceptions\InvalidFieldTypeException;
use Popuper\Models\Templates\FieldTypes as FieldTypesModel;

/**
 * Class Factory
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
class Factory
{
    /**
     * @param ElementInterface $element
     *
     * @return Checkbox|Text
     * @throws InvalidFieldTypeException
     */
    public static function get(ElementInterface $element): FieldTypeInterface
    {
        switch ($element->fieldTypeId) {
            case FieldTypesModel::ID_FIELD_TYPE_CHECKBOX_CHECKED:
                return new Checkbox($element);

            case FieldTypesModel::ID_FIELD_TYPE_LINK:
            case FieldTypesModel::ID_FIELD_TYPE_TITLE:
                return new Text($element);

           case FieldTypesModel::ID_FIELD_TYPE_SELECT:
                return new Select($element);
        }

        throw new InvalidFieldTypeException('Invalid Field Type.');
    }

}