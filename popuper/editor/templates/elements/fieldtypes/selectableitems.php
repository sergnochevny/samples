<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

use Leads\Request\Types as LeadsRequestTypesModel;
use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Editor\Templates\Exceptions\InvalidFieldException;
use Popuper\Helpers\DataKeys;
use Popuper\Models\Templates\Fields as FieldsModel;
use Popuper\Models\Templates\FieldTypes;

/**
 * Class SelectableItems
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
class SelectableItems
{
    /**
     * @param ElementInterface $element
     *
     * @return string
     * @throws InvalidFieldException
     */
    public static function getItemsKey(ElementInterface $element): string
    {
        if($element->fieldTypeId == FieldTypes::ID_FIELD_TYPE_SELECT){
            switch ($element->entityId){
                case FieldsModel::ID_FIELD_LEAD_REQUEST_TYPE:
                    return DataKeys::LEAD_REQUEST_TYPES;
            }
        }

        throw new InvalidFieldException('Wrong getting selectable list of field:' . $element->entityId);
    }

    /**
     * @param ElementInterface $element
     *
     * @return array
     */
    public static function getAvailableValues(ElementInterface $element): array
    {
        if($element->fieldTypeId == FieldTypes::ID_FIELD_TYPE_SELECT){
            switch ($element->entityId){
                case FieldsModel::ID_FIELD_LEAD_REQUEST_TYPE:
                    return array_keys(LeadsRequestTypesModel::getItems());
            }
        }

        return [];
    }
}