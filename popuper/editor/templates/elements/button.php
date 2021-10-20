<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class Button
 *
 * @package Popuper\Editor\Templates\Elements
 */
class Button extends ElementAbstract
{
    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [
        DataKeys::ID_KEY,
        DataKeys::LABEL_KEY,
        DataKeys::DESCRIPTION_KEY,
        DataKeys::ACTION_TYPE_KEY,
        DataKeys::DEFAULT_TITLE_KEY,
    ];

    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_BUTTON;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::BUTTONS_KEY;
    }

}