<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class Style
 *
  * @package Popuper\Editor\Templates\Elements
 */
class Style extends ElementAbstract
{
    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_TEMPLATE_STYLE;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::STYLES_KEY;
    }

}