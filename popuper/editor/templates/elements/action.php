<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class Action
 *
 * @package Popuper\Editor\Templates\Elements
 */
class Action extends ElementAbstract
{
    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [
        DataKeys::ID_KEY,
        DataKeys::LABEL_KEY,
    ];

    /**
     * @return string
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_ACTION;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::ACTIONS_KEY;
    }

}