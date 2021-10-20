<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

/**
 * Interface SelectTypeInterface
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
interface SelectTypeInterface extends FieldTypeInterface
{
    /**
     * @return array
     */
    public function getAvailableValues(): array;
}