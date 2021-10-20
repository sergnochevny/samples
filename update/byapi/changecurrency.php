<?php

namespace Leads\Update\ByApi;

use Leads\Update\ByApi\SingleField as SingleFieldUpdate;
use Leads\Update\Common\FieldNames;

/**
 * Class ChangeCurrency
 *
 * @package Leads\Update\ByApi
 */
class ChangeCurrency extends SingleFieldUpdate
{
    /**
     * @var integer
     */
    protected $_fieldName = FieldNames::FIELD_CURRENCY;

}