<?php

namespace Leads\Update\ByApi;

use Leads\Update\Common\BuilderInterface;
use Leads\Update\Common\SingleField as BaseSingleField;

/**
 * Class SingleField
 *
 * @package Leads\Update\ByApi
 */
class SingleField extends BaseSingleField
{
    /**
     * SingleField constructor.
     *
     * @param $leadId
     */
     public function __construct($leadId)
    {
        $this->_leadId = $leadId;
    }

    /**
     * @return BuilderInterface
     */
    protected function _getFieldSetBuilder(): BuilderInterface
    {
        return new FieldSetsBuilder();
    }
}