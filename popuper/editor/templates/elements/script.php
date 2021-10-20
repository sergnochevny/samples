<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class Script
 *
  * @package Popuper\Editor\Templates\Elements
 */
class Script extends ElementAbstract
{
    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_TEMPLATE_SCRIPT;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::SCRIPTS_KEY;
    }

}