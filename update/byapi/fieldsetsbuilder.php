<?php

namespace Leads\Update\ByApi;

use Leads\Update\Actions\NonThrowable\AutoAssignRules;
use Leads\Update\Actions\NonThrowable\AutoDepartmentUpdateQueue;
use Leads\Update\Actions\NonThrowable\ChangeAffiliateIsTestStatus;
use Leads\Update\Actions\NonThrowable\ExternalSystemDeferred;
use Leads\Update\Actions\NonThrowable\NewAssignLeadNotify;
use Leads\Update\Actions\NonThrowable\ReassignReminders;
use Leads\Update\Actions\NonThrowable\SetNotApprovedWithdrawalGroups;
use Leads\Update\Actions\NonThrowable\UpdateLeadEvent;
use Leads\Update\Actions\NonThrowable\UpdateLeadMailOuts;
use Leads\Update\Actions\NonThrowable\UpdateLeadOnAffiliateProgram;
use Leads\Update\Actions\Throwable\Before\SaveOperations\Leads as LeadsOperations;
use Leads\Update\Actions\Throwable\Update\DepartmentUpdate;
use Leads\Update\Actions\Throwable\Update\UpdateLeadData;
use Leads\Update\ByAgent\FieldSetsBuilder as BaseFieldSetsBuilder;
use Leads\Update\Common\FieldNames;
use Leads\Update\DataPreparers\Department as DepartmentPreparer;
use Leads\Update\Exceptions\InvalidField;
use Leads\Update\Exceptions\InvalidField as InvalidFieldException;
use Leads\Update\Field\FieldSetInterface;
use Model_Leads;

/**
 * Class FieldSetsBuilder
 *
 * @package Leads\Update\ByApi
 */
class FieldSetsBuilder extends BaseFieldSetsBuilder
{
    const FIELDS_INCOMING_TO_DB_FIELDS_MAP = [
        FieldNames::FIELD_CURRENCY => Model_Leads::FIELD_CURRENCY,
        FieldNames::FIELD_IS_TEST => Model_Leads::FIELD_IS_TEST,
        FieldNames::FIELD_DEPARTMENT => Model_Leads::FIELD_DEPARTMENT,
        FieldNames::FIELD_DEPARTMENT_WO_AUTO_ASSIGN => Model_Leads::FIELD_DEPARTMENT,
    ];

    /**
     * @param $fieldName
     *
     * @return FieldSetInterface
     * @throws InvalidFieldException
     * @throws \Exception
     */
    public function build($fieldName): FieldSetInterface
    {
        $fieldDBName = $this->_getFieldDBName($fieldName);

        switch ($fieldName) {
            case FieldNames::FIELD_CURRENCY:
                return $this->_newFieldSet($fieldName, $this->_getLeadStore($fieldDBName))
                    ->addActions(
                        [
                            LeadsOperations::class,
                            UpdateLeadData::class,
                            UpdateLeadEvent::class,
                            UpdateLeadOnAffiliateProgram::class,
                            UpdateLeadMailOuts::class,
                            ExternalSystemDeferred::class,
                        ]
                    );

            case FieldNames::FIELD_IS_TEST:
                return $this->_newFieldSet($fieldName, $this->_getLeadStore($fieldDBName))
                    ->addActions(
                        [
                            LeadsOperations::class,
                            UpdateLeadData::class,
                            SetNotApprovedWithdrawalGroups::class,
                            UpdateLeadOnAffiliateProgram::class,
                            ChangeAffiliateIsTestStatus::class,
                            UpdateLeadEvent::class,
                            UpdateLeadMailOuts::class,
                            ExternalSystemDeferred::class,
                        ]
                    );

            case FieldNames::FIELD_DEPARTMENT :
                return $this->_newFieldSet($fieldName, $this->_getLeadStore($fieldDBName))
                    ->setDataPreparer(new DepartmentPreparer())
                    ->addActions(
                        [
                            LeadsOperations::class,
                            UpdateLeadData::class,
                            DepartmentUpdate::class,
                            UpdateLeadEvent::class,
                            AutoAssignRules::class,
                            AutoDepartmentUpdateQueue::class,
                            UpdateLeadMailOuts::class,
                            NewAssignLeadNotify::class,
                            ReassignReminders::class,
                        ]
                    );

            case FieldNames::FIELD_DEPARTMENT_WO_AUTO_ASSIGN :
                return $this->_newFieldSet($fieldName, $this->_getLeadStore($fieldDBName))
                    ->setDataPreparer(new DepartmentPreparer())
                    ->addActions(
                        [
                            LeadsOperations::class,
                            UpdateLeadData::class,
                            DepartmentUpdate::class,
                            UpdateLeadEvent::class,
                            AutoDepartmentUpdateQueue::class,
                            UpdateLeadMailOuts::class,
                            NewAssignLeadNotify::class,
                            ReassignReminders::class,
                        ]
                    );
        }

        throw new \Exception('Field saving rules does not exist');
    }

}