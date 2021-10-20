<?php

namespace Popuper\Editor\Templates\Elements\FieldTypes;

/**
 * Interface FieldTypeInterface
 *
 * @package Popuper\Editor\Templates\Elements\FieldTypes
 */
interface FieldTypeInterface
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function castValue($value);

    /**
     * @return string
     */
    public function getInputType(): string;

    /**
     * @return array
     */
    public function getTemplateData(): array;
}