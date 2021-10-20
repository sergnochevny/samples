<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

use Popuper\Helpers\DataKeys;

/**
 * Class Checkbox
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
class Checkbox extends Text
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function castValue($value){
        return ((bool)$value) ? '1' : null;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return DataKeys::INPUT_TYPE_CHECKBOX;
    }
}