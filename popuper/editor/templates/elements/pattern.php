<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class Pattern
 *
  * @package Popuper\Editor\Templates\Elements
 */
class Pattern extends ElementAbstract
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
        return EntityTypesModel::ENTITY_PATTERN;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::PATTERNS_KEY;
    }

}