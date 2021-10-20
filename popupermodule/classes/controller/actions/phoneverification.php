<?php

use Controller\JsonTemplateAbstract;
use Leads\PhoneVerification\Exception\PhoneVerificationException;
use Leads\PhoneVerification\Exception\PhoneVerificationNotFoundException;
use Leads\PhoneVerification\Model\PhoneVerification;
use Leads\PhoneVerification\Model\PhoneVerificationCodes;
use Leads\PhoneVerification\PhoneVerificationManager;
use SystemTranslation\Models\SystemText;
use SystemTranslation\Models\SystemTextsVariable;
use SystemTranslation\Translation;

/**
 * Class Controller_Actions_PhoneVerification
 */
class Controller_Actions_PhoneVerification extends JsonTemplateAbstract
{
    const INPUT_PARAM_NAME = 'phoneVerificationCode';

    /**
     * @throws Kohana_Exception
     * @throws Throwable
     * @throws \Events\Exceptions\StopPropagationException
     */
    public function action_check()
    {
        if (!$leadId = (int) $this->_getLeadId()) {
            return;
        }

        $code = trim((string) ($_POST[static::INPUT_PARAM_NAME] ?? ''));

        try {
            $manager = PhoneVerificationManager::getInstance($leadId);
            $manager->checkCode($code);
            $this->_setResponse(true);
        } catch (PhoneVerificationException $e) {
            $this->_setErrorResponse($manager, $e->getCode());
        } catch (PhoneVerificationNotFoundException $e) {
            $this->_setResponse(false, $this->_getErrorMessage(SystemText::SMS_VERIFICATION_SYSTEM_ERROR), true);
        }
    }

    /**
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     * @throws Throwable
     * @throws \Currency\Exceptions\InvalidCurrencyException
     * @throws \Currency\Exceptions\InvalidCurrencyFieldException
     * @throws \Events\Exceptions\StopPropagationException
     * @throws \Sms\Exceptions\LeadsPhonesException
     */
    public function action_resend()
    {
        if (!$leadId = (int) $this->_getLeadId()) {
            return;
        }

        try {
            $manager = PhoneVerificationManager::getInstance($leadId);
            $manager->sendCode();
            $this->_setResponse(false); // status = false, popup dose not close
        } catch (PhoneVerificationException $e) {
            $this->_setErrorResponse($manager, $e->getCode());
        } catch (PhoneVerificationNotFoundException $e) {
            $this->_setResponse(false, $this->_getErrorMessage(SystemText::SMS_VERIFICATION_SYSTEM_ERROR), true);
        }
    }

    /**
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     * @throws Throwable
     * @throws \Common\Traits\RecordSetIdException
     * @throws \Events\Exceptions\StopPropagationException
     * @throws \Exceptions\ClientException
     */
    public function action_close()
    {
        if (!$leadId = (int) $this->_getLeadId()) {
            return;
        }

        try {
            PhoneVerificationManager::getInstance($leadId)->setStatusClose();
        } catch (PhoneVerificationException $e) {
            F::notifyTechError($e);
        } catch (PhoneVerificationNotFoundException $e) {
        }

        $this->_setResponse(true);
    }

    /**
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     * @throws Throwable
     * @throws \Common\Traits\RecordSetIdException
     * @throws \Events\Exceptions\StopPropagationException
     * @throws \Exceptions\ClientException
     */
    public function action_nonMobile()
    {
        if (!$leadId = (int) $this->_getLeadId()) {
            return;
        }

        try {
            $manager = PhoneVerificationManager::getInstance($leadId);
            $manager->setStatusNonMobile();
            $this->_setResponse(true);
        } catch (PhoneVerificationException $e) {
            $this->_setErrorResponse($manager, $e->getCode());
        } catch (PhoneVerificationNotFoundException $e) {
            $this->_setResponse(false, $this->_getErrorMessage(SystemText::SMS_VERIFICATION_SYSTEM_ERROR), true);
        }
    }

    /**
     * @param PhoneVerificationManager $manager
     * @param int                      $code
     *
     * @throws Exception
     */
    protected function _setErrorResponse(PhoneVerificationManager $manager, int $code)
    {
        switch ($code) {
            case PhoneVerificationException::AUTO_SEND_EVENT_NOT_FOUND:
            case PhoneVerificationException::SMS_NOT_SEND:
                $this->_setResponse(
                    false,
                    $this->_getErrorMessage(SystemText::SMS_SENDING_FAILED)
                );
                break;
            case PhoneVerificationException::MAX_SENT_SMS_COUNT:
                $this->_setResponse(
                    false,
                    $this->_getErrorMessage(
                        SystemText::MAX_ATTEMPTS_SEND_VERIFICATION_CODE,
                        [
                            SystemTextsVariable::VARIABLE_MAXIMUM_ATTEMPTS_RESEND => PhoneVerification::getMaxAttemptsToSend(),
                            SystemTextsVariable::VARIABLE_PHONE => $manager->getPhoneNumber(),
                        ]
                    ),
                    true
                );
                break;
            case PhoneVerificationException::MAX_CHECK_CODE_COUNT:
                $this->_setResponse(
                    false,
                    $this->_getErrorMessage(
                        SystemText::MAX_ATTEMPTS_CHECK_VERIFICATION_CODE,
                        [
                            SystemTextsVariable::VARIABLE_MAXIMUM_ATTEMPTS_CHECK => PhoneVerificationCodes::getMaxAttemptsToCheck(),
                        ]
                    ),
                    true
                );
                break;
            case PhoneVerificationException::EXPIRED_VERIFICATION_CODE:
                $this->_setResponse(
                    false,
                    $this->_getErrorMessage(SystemText::EXPIRED_VERIFICATION_CODE)
                );
                break;
            case PhoneVerificationException::INVALID_VERIFICATION_CODE:
                $this->_setResponse(
                    false,
                    $this->_getErrorMessage(SystemText::INVALID_VERIFICATION_CODE)
                );
                break;
            case PhoneVerificationException::CURRENT_STATUS_NOT_PENDING:
            case PhoneVerificationException::EVENT_NOT_ACTUAL:
                $this->_setResponse(
                    false,
                    $this->_getErrorMessage(SystemText::SMS_VERIFICATION_SYSTEM_ERROR),
                    true
                );
                break;
        }
    }

    /**
     * Get error massage translation
     *
     * @param int   $id
     * @param array $variables
     *
     * @return string
     * @throws Exception
     */
    protected function _getErrorMessage(int $id, array $variables = []):string
    {
        return Translation::getTranslation($id, (string) $this->_getLanguage(), $variables);
    }

    /**
     * Set response data
     *
     * @param bool   $status
     * @param string $message
     * @param bool   $disableButtons
     */
    protected function _setResponse(bool $status = true, string $message = '', bool $disableButtons = false)
    {
        $data['success'] = $status;
        $data['disableButtons'] = $disableButtons;
        if ($message) {
            $data['messages'][static::INPUT_PARAM_NAME] = $message;
        }
        $this->template->result = $data;
    }
}