<?php

use Controller\JsonTemplateAbstract;
use Leads\SpecificField\SubscribedNewsletterByEmail;
use leads\SpecificFields;
use Model\Leads\DataPolicy\PrivacyPolicy;
use Model\Leads\DataPolicy\Statuses;
use Popuper\EventsManager as EventsManager;
use Popuper\Models\Events\Types as EventTypesModel;
use Popuper\Models\Events\Variables as EventVariablesModel;

/**
 * Class Controller_Actions_Terms
 *
 * Controller with actions which called from
 * pop-ups(accept or decline T&C, check captcha, etc)
 *
 */
class Controller_Actions_Terms extends JsonTemplateAbstract
{
    /**
     * Function action_termsAccept
     *
     * @throws Exception
     */
    public function action_termsAccept()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_termsAcceptApply();
    }

    /**
     * @throws Exception
     */
    public function action_termsMarketing()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_subscribedNewsletterStatusApply();
        $this->_termsAcceptApply();
    }

    /**
     * @throws Exception
     */
    public function action_termsNotUSReportable()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_notUSReportableApply();
        $this->_termsAcceptApply();
    }

    /**
     * @throws Exception
     */
    public function action_termsDataPolicy()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_privacyPolicyAgreedApply();
        $this->_termsAcceptApply();
    }

    /**
     * @throws Exception
     */
    public function action_termsDataPolicyMarketing()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_privacyPolicyAgreedApply();
        $this->_subscribedNewsletterStatusApply();
        $this->_termsAcceptApply();
    }

    /**
     * Function action_termsCaptcha
     *
     * @throws Exception
     */
    public function action_termsCaptcha()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_checkCaptcha();
        $this->_termsAcceptApply();
    }

    /**
     * Function action_gdprRegulation
     *
     * @throws Exception
     */
    public function action_dataPolicy()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_privacyPolicyAgreedApply();
    }

    /**
     * Function action_gdprRegulation
     *
     * @throws Exception
     */
    public function action_dataPolicyMarketing()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_privacyPolicyAgreedApply();
        $this->_subscribedNewsletterStatusApply();
    }

    /**
     * Function action_subscribedNewsletter
     *
     * @return void
     * @throws Exception
     */
    public function action_subscribedNewsletter()
    {
        if (!$this->_getLeadId()) {
            return;
        }

        $this->_checkPopupId();

        if ($this->_subscribedNewsletterStatusApply()) {
            $this->_changePermanentEventForPopup();
        }
    }

    /**
     * @throws Exception
     */
    protected function _termsAcceptApply()
    {
        if (!$this->template->result['success']) {
            return;
        }

        $newStatus = ($_POST['termsConditions'] ?? null)
            ? Model_LeadsTermsAcceptanceStatuses::AGREED
            : Model_LeadsTermsAcceptanceStatuses::NOT_AGREED;

        if (!$this->_setTermsAcceptStatus($newStatus)) {
            $this->template->result['messages'] = $this->_getErrorMessages('termsConditions');
            $this->template->result['success'] = false;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function _subscribedNewsletterStatusApply()
    {
        if (!$this->template->result['success']) {
            return false;
        }

        return (new SubscribedNewsletterByEmail($this->_getLeadId()))
            ->change($_POST['marketing'] ?? false);
    }

    /**
     * @throws Exception
     */
    protected function _privacyPolicyAgreedApply()
    {
        if (!$this->template->result['success']) {
            return;
        }

        if (!$this->_setPrivacyPolicyAgreed($_POST['dataPolicy'] ?? null)) {
            $this->template->result['messages'] = $this->_getErrorMessages('dataPolicy');
            $this->template->result['success'] = false;
        }
    }

    /**
     * check captcha
     *
     */
    protected function _checkCaptcha()
    {
        if (!$this->template->result['success']) {
            return;
        }

        if (!SimpleCaptcha::valid($_POST['captchaResponse'] ?? '')) {
            $this->template->result['messages'] = $this->_getErrorMessages('captchaResponse');
            $this->template->result['success'] = false;
        }
    }

    /**
     * @throws Exception
     */
    protected function _notUSReportableApply()
    {
        if (!$this->template->result['success']) {
            return;
        }

        if (!$this->_setNotUSReportablePerson($_POST['notUSReportablePerson'] ?? null)) {
            $this->template->result['messages'] = $this->_getErrorMessages('notUSReportablePerson');
            $this->template->result['success'] = false;
        }
    }

    /**
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     * @throws Exception
     */
    protected function _changePermanentEventForPopup()
    {
        if (!$this->template->result['success']) {
            return;
        }

        $manager = new EventsManager();

        $event = $manager->getEvent(
            EventTypesModel::EVENT_UNSPECIFIED_PERMANENT,
            $this->_getLeadId()
        );

        $eventVariablesValues = $event->getVariablesValues();

        $originalValues = $eventVariablesValues[EventVariablesModel::CUSTOM_POPUP_IDS_LIST] ?? [];
        $values = array_diff($originalValues, [$_POST['popupId']]);

        if ($values) {
            if ($originalValues != $values) {
                $event->setVariablesValues([EventVariablesModel::CUSTOM_POPUP_IDS_LIST => $values]);

                $this->template->result['success'] = $manager->saveEvent($event);
            }
        } else {
            $this->template->result['success'] = $manager->removeEvent($event);
        }
    }

    /**
     * Function _termsAccept
     *
     * @param $newStatus
     *
     * @return bool
     * @throws Exception
     *
     */
    protected function _setTermsAcceptStatus($newStatus)
    {
        if ($newStatus != Model_LeadsTermsAcceptanceStatuses::AGREED) {
            return false;
        }

        $leadId = $this->_getLeadId();

        /** @var array $currentStatus */
        $currentStatus = Arr::get(
            Model_LeadsTermsAcceptanceStatus::getByLead($leadId),
            'statusId',
            Model_LeadsTermsAcceptanceStatuses::NOT_SEEN
        );

        if ($currentStatus != $newStatus) {
            /** @var bool $result */
            $result = Model_LeadsTermsAcceptanceStatus::setStatusByLead(
                $leadId,
                $newStatus,
                $this->_getIpAddress(),
                Request::$user_agent
            );
        } else {
            $result = EventsManager::setEvent(
                EventTypesModel::EVENT_RE_ACCEPT_TERMS,
                $leadId,
                [EventVariablesModel::LEAD_TC_STATUS_ID => $newStatus]
            );
        }

        return $result;
    }

    /**
     * @param $dataPolicy
     *
     * @return bool
     * @throws Exception
     */
    protected function _setPrivacyPolicyAgreed($dataPolicy)
    {
        if ($dataPolicy === null) {
            return false;
        }

        return (new PrivacyPolicy($this->_getLeadId()))
            ->change(Statuses::AGREED);
    }

    /**
     * @param $notUSReportablePerson
     *
     * @return bool
     * @throws Exception
     */
    protected function _setNotUSReportablePerson($notUSReportablePerson)
    {
        if ($notUSReportablePerson === null) {
            return false;
        }

        $leadId = $this->_getLeadId();
        $leadInfo = Leads::get_lead_info($leadId);
        $leadsCountryType = $leadInfo['country_type'] ?? null;
        $enableUSFATCAOptions = SystemOptions::get('Leads/Registration/Enable US FATCA checkbox agreement');

        if (
            $enableUSFATCAOptions
            && ($leadsCountryType !== null)
            && ($enableUSFATCAOptions[$leadsCountryType] ?? ($enableUSFATCAOptions['default'] ?? null))
        ) {
            $leadSpecificFields = new SpecificFields($leadId);
            $leadSpecificFields->notUSReportablePerson = $notUSReportablePerson;

            return $leadSpecificFields->save();
        }

        return true;
    }

    /**
     *
     */
    protected function _checkPopupId()
    {
        $this->template->result['success'] = (bool)($_POST['popupId'] ?? null);
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

}