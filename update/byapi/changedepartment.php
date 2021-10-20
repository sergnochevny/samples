<?php

namespace Leads\Update\ByApi;

use Leads\Update\ByApi\SingleField as SingleFieldUpdate;
use Leads\Update\Common\FieldNames;

class ChangeDepartment extends SingleFieldUpdate
{
    /**
     * ChangeDepartment constructor.
     *
     * @param      $leadId
     * @param bool $withAssign
     */
    public function __construct($leadId, $withAssign = true)
    {
        parent::__construct($leadId);

         $this->_fieldName = $withAssign
             ? FieldNames::FIELD_DEPARTMENT
             : FieldNames::FIELD_DEPARTMENT_WO_AUTO_ASSIGN;
    }

}