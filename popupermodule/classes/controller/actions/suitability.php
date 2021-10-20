<?php

use Controller\JsonTemplateAbstract;
use Popuper\Events\SuitabilityCalculation;
use Popuper\EventsManager as EventsManager;
use Popuper\Models\Events\Types as EventTypesModel;
use Suitability\Manager as SuitabilityManager;
use SuspendReasonsConfigurator\SuspendReason;

/**
 * Class Controller_Actions_Suitability
 *
 * Controller with actions which called from
 * pop-ups(accept or decline T&C, check captcha, etc)
 *
 */
class Controller_Actions_Suitability extends JsonTemplateAbstract
{
    /**
     * @throws Exception
     */
    public function action_noticeApprove()
    {
        $this->_suitabilityNotificationProcessing($_POST['value'] ?? null);
    }

    /**
     * @throws Exception
     */
    public function action_levelAgreement()
    {
        $this->_suitabilityNotificationProcessing(
            $_POST['value'] ?? null,
            function () {
                /** For agreement - Unsuspend this reason */
                (new LeadsSuspend($this->_getLeadId(), false))
                    ->unsuspend(
                        [SuspendReason::REASON_AUTO_SUSPENDED_UNTIL_THE_SUITABILITY_LEVEL_AGREEMENT]
                    );

                return true;
            }
        );
    }

    /**
     * @throws Exception
     */
    public function action_leverageIncrease()
    {
        $this->_suitabilityNotificationProcessing(
            $_POST['value'] ?? null,
            function () {
                SuitabilityManager::factory(
                    SuitabilityManager::LEAD_ACTIVITY_CALCULATION
                )
                    ->setMaxLeverageOfCurrentLevel($this->_getLeadId())
                    ->sendToPlatformDeferred();

                return true;
            }
        );
    }

    /**
     * @param callable|null $approveCallback
     * @param callable|null $declineCallback
     * @param               $value
     *
     * @throws Exception
     */
    protected function _suitabilityNotificationProcessing(
        $value,
        callable $approveCallback = null,
        callable $declineCallback = null
    ) {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_checkRequestValue($value);
        $this->_notificationApply($value, $approveCallback, $declineCallback);
        $this->_removeSuitabilityCalculationEvent();
    }

    /**
     * @param $value
     */
    protected function _checkRequestValue($value)
    {
        /** Invalid request */
        if (!in_array($value, SuitabilityCalculation::$availableNotifyStatuses)) {
            $this->template->result['success'] = false;
            $this->template->result['messages'] = ['error' => 'Invalid request'];
        }
    }

    /**
     * @param          $value
     * @param callable $approveCallback
     * @param callable $declineCallback
     */
    protected function _notificationApply($value, callable $approveCallback = null, callable $declineCallback = null)
    {
        if (!$this->template->result['success']) {
            return;
        }

        if ($value == SuitabilityCalculation::INCREASE_APPROVED && $approveCallback !== null) {
            $this->template->result['success'] = call_user_func($approveCallback);
        } elseif ($value == SuitabilityCalculation::INCREASE_NOT_APPROVED && $declineCallback !== null) {
            $this->template->result['success'] = call_user_func($declineCallback);
        }
    }

    /**
     * @return bool
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     * @throws Exception
     */
    protected function _removeSuitabilityCalculationEvent()
    {
        if (!$this->template->result['success']) {
            return false;
        }

        return EventsManager::dropEvent(
            EventTypesModel::EVENT_SUITABILITY_CALCULATION,
            $this->_getLeadId()
        );
    }

}