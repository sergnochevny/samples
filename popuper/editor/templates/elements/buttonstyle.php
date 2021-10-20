<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class ButtonStyle
 *
  * @package Popuper\Editor\Templates\Elements
 */
class ButtonStyle extends ElementAbstract
{
    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [
        DataKeys::ID_KEY,
        DataKeys::LABEL_KEY,
        DataKeys::ACTION_TYPE_KEY,
    ];


    /**
     * @return string
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_BUTTON_STYLE;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::BUTTON_STYLES_KEY;
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return $this->_entityId;
    }

}