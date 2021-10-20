<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class ShortCode
 *
  * @package Popuper\Editor\Templates\Elements
 */
class ShortCode extends ElementAbstract
{
    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [
        DataKeys::ID_KEY,
        DataKeys::LABEL_KEY,
        DataKeys::DESCRIPTION_KEY,
        DataKeys::IS_MANDATORY_KEY,
    ];


    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_SHORT_CODE;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::SHORT_CODES_KEY;
    }

}