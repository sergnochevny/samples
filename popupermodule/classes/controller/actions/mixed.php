<?php

use Assessment\Status\Lead;
use Assessment\Status\Model\Statuses;
use Controller\JsonTemplateAbstract;
use Events\Exceptions\StopPropagationException;
use Lead\Dispatcher;
use Lead\Event\RequestFromLead as RequestFromLeadEvent;
use Leads\SpecificFields as LeadsSpecificFields;
use Popuper\EventsManager as EventsManager;
use Popuper\Models\Events\Types as EventTypesModel;
use SuspendReasonsConfigurator\SuspendReason;

/**
 * Class Controller_Actions_Mixed
 *
 * Controller with actions which called from
 * pop-ups(accept or decline T&C, check captcha, etc)
 */
class Controller_Actions_Mixed extends JsonTemplateAbstract
{
    /**
     * @throws Throwable
     */
    public function action_cnmvAcknowledgement()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_checkCnmvAcknowledgementValues();

        $this->_setCNMVRegulationConfirmationText();
        $this->_cnmvRegulationConfirmationApply();
    }

    /**
     * Function action_howToTrade
     *
     * @return void
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     * @throws Throwable
     */
    public function action_howToTrade()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_checkRequestValues();

        $this->_setHowToTradeStatus();
        $this->_howToTradeStatusApply();
    }

    /**
     * @throws Throwable
     */
    public function action_leadRequest()
    {
        if (!$this->_getLeadId()) {
            return;
        }
        $requestTypeId = $_POST['requestTypeId'] ?? null;
        $popupId = $_POST['popupId'] ?? null;

        if (!$requestTypeId || !$popupId) {
            $this->template->result['success'] = false;
            return;
        }

        $this->_triggerRequestFromLeadEvent($requestTypeId, $popupId, $_POST['address'] ?? null);

        $this->template->result['success'] = true;
    }

    /**
     * @return mixed|null
     */
    protected function _checkRequestValues()
    {
        $postValue = $_POST['value'] ?? null;

        if ($postValue === null) {
            $this->template->result['success'] = false;
        }
    }

    /**
     *
     */
    protected function _checkCnmvAcknowledgementValues()
    {
        $postValue = trim($_POST['cnmvAcknowledgement'] ?? null);

        // check for empty or wrong length value
        if (mb_strlen($postValue) === 0 || mb_strlen($postValue) > 255) {
            $this->template->result['success'] = false;
            $this->template->result['messages'] = $this->_getErrorMessages('cnmvAcknowledgement');
        }
    }

    /**
     * @throws Kohana_Exception
     * @throws Throwable
     */
    protected function _howToTradeStatusApply()
    {
        if (!$this->template->result['success']) {
            return;
        }

        $leadId = $this->_getLeadId();

        if ($_POST['value']) {
            (new LeadsSuspend($leadId, false))->unsuspend(
                [SuspendReason::REASON_QUIZ_IS_NOT_COMPLETE]
            );

            (new Lead($leadId))
                ->setStatusId(Statuses::ID_NOT_APPLICABLE)
                ->save();

            $comment = 'Defined as experienced trader';
        } else {
            $comment = 'Defined as not experienced trader';
        }

        Operations::save(
            OperationsTypes::OTHER,
            'lead',
            $leadId,
            '',
            $comment
        );

        EventsManager::dropEvent(
            EventTypesModel::EVENT_HOW_TO_TRADE,
            $leadId
        );
    }

    /**
     * @throws Throwable
     */
    protected function _cnmvRegulationConfirmationApply()
    {
        if (!$this->template->result['success']) {
            return;
        }

        (new LeadsSuspend($this->_getLeadId(), 0))
            ->unsuspend([SuspendReason::REASON_AUTO_SUSPEND_CNMV_ACKNOWLEDGEMENT]);
    }

    /**
     * @throws Throwable
     */
    protected function _setHowToTradeStatus()
    {
        if (!$this->template->result['success']) {
            return;
        }

        $leadSpecificFields = new LeadsSpecificFields($this->_getLeadId());
        $leadSpecificFields->knowHowToTrade = $_POST['value'];

        $this->template->result['success'] = $leadSpecificFields->save();
    }

    /**
     * @throws Throwable
     */
    protected function _setCNMVRegulationConfirmationText()
    {
        if (!$this->template->result['success']) {
            return;
        }

        $leadSpecificFields = new LeadsSpecificFields($this->_getLeadId());
        $leadSpecificFields->CNMVRegulationConfirmationText = HTML::entities(trim($_POST['cnmvAcknowledgement']));

        $this->template->result['success'] = $leadSpecificFields->save();
    }

    /**
     * @param $errorId
     *
     * @return array
     */
    protected function _getErrorMessages($errorId): array
    {
        return [$errorId => Kohana::message('popups', $errorId)];
    }

    /**
     * @param int $requestTypeId
     * @param int $popupId
     * @param string $address
     *
     * @throws StopPropagationException
     * @throws Throwable
     */
    protected function _triggerRequestFromLeadEvent($requestTypeId, $popupId, $address)
    {
        Dispatcher::trigger(
            RequestFromLeadEvent::NAME,
            new RequestFromLeadEvent(
                $this->_getLeadId(),
                $requestTypeId,
                $address ?: $this->_referrer,
                $popupId
            )
        );
    }

}