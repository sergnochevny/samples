<?php

namespace Leads\Update\Common;

use Leads\Update\Exceptions\InvalidField;
use Leads\Update\Exceptions\InvalidField as InvalidFieldException;
use Leads\Update\Field\FieldSetInterface;

/**
 * Interface BuilderInterface
 *
 * @package Leads\Update\Common
 */
interface BuilderInterface
{
    /**
     * @param $fieldName
     *
     * @return FieldSetInterface
     * @throws InvalidFieldException
     */
    public function build($fieldName): FieldSetInterface;
}