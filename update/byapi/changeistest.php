<?php

namespace Leads\Update\ByApi;

use Leads\Update\ByApi\SingleField as SingleFieldUpdate;
use Leads\Update\Common\FieldNames;

/**
 * Class ChangeIsTest
 *
 * @package Leads\Update\ByApi
 */
class ChangeIsTest extends SingleFieldUpdate
{
    /**
     * @var integer
     */
    protected $_fieldName = FieldNames::FIELD_IS_TEST;

    /**
     * @return null
     */
    protected function _getValidator()
    {
        return null;
    }

}