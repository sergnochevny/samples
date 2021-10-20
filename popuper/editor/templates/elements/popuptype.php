<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class PopupType
 *
  * @package Popuper\Editor\Templates\Elements
 */
class PopupType extends ElementAbstract
{
    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [
        DataKeys::ID_KEY,
        DataKeys::LABEL_KEY,
    ];

    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_POPUP_TYPE;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::POPUP_TYPES_KEY;
    }

}