<?php

namespace Controller;

use Arr;
use Cookie;
use Exception;
use F;
use Kohana;
use Language;
use Leads\Authorization\AuthByCookie;
use Leads\Authorization\AuthException;
use Leads_CountryType;
use Model_CountryType;
use Model_Language;
use Popuper\PopupData;
use Popuper\Variables\RequestData;
use Request;
use Session;
use Throwable;
use URL;

/**
 * Trait CollectParameters
 *
 * Collect parameters trait for pop-up with business layer.
 *
 */
trait CollectParameters
{
    /** @var string */
    protected $_ip;

    /** @var string */
    protected $_countryByIP;

    /** @var string */
    protected $_referrer;

    /** @var int|null */
    protected $_leadId;

    /** @var int|null */
    protected $_nonAuthId;

    /** @var string|null _language Current site's language. As default en */
    protected $_language;

    /** @var string */
    protected $_callerPageData;

    /** @var array */
    protected $_eventsShownPerPage;

    /** @var array */
    protected $_shownPopupsPerSession;

    /** @var string|null */
    protected $_lastNotifiedUnsupportedCountry;

    /** @var RequestData */
    protected $_requestData;

    /**
     * @throws Exception
     * @throws Throwable
     */
    protected function _loadParameters()
    {
        $this->_getRequestData();
    }

    /**
     * @return array|mixed
     */
    protected function _getCallerPageData()
    {
        if ($this->_callerPageData === null) {
            $callerPageData = $_GET['page'] ?? null;
            $this->_callerPageData = is_array($callerPageData) ? $callerPageData : [];
        }

        return $this->_callerPageData;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function _getEventsShownPerPage()
    {
        if ($this->_eventsShownPerPage === null) {
            $currentlyShown = $_POST['log'] ?? [];
            $this->_eventsShownPerPage = is_array($currentlyShown) ? array_unique($currentlyShown) : [];
        }

        return $this->_eventsShownPerPage;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getShownPopupsPerSession()
    {
        if ($this->_shownPopupsPerSession === null) {
            $this->_shownPopupsPerSession = $this->_loadShownPopupsFromSession();
        }

        return $this->_shownPopupsPerSession;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function _updateShownPopupsPerSession()
    {
        Session::instance()->set('shownPopups', $this->_getShownPopupsPerSession());
    }

    /**
     * @return string
     */
    protected function _getReferrer()
    {
        if ($this->_referrer === null) {
            $this->_referrer = (Request::$referrer)
                ? : (
                    URL::base(false, true) .
                    trim(Arr::get($_SERVER, 'REQUEST_URI'), '/')
                );
        }

        return $this->_referrer;
    }

    /**
     * @return int|null
     * @throws Exception
     * @throws Throwable
     */
    protected function _getLeadId()
    {
        if ($this->_leadId === null) {
            $this->_leadId = $this->_loadLeadIdByCookies();
        }

        return $this->_leadId;
    }

    /**
     * @return int|null
     * @throws Exception
     * @throws Throwable
     */
    protected function _getNonAuthId()
    {
        if ($this->_nonAuthId === null) {
            $this->_nonAuthId = $this->_loadNonAuthIdByCookies();
        }

        return $this->_nonAuthId;
    }

    /**
     * @return bool|string|null
     * @throws Exception
     */
    protected function _getLanguage()
    {
        if ($this->_language === null) {
            $this->_language = $this->_detectLanguage();
        }

        return $this->_language;
    }

    /**
     * @return mixed|string|null
     * @throws Exception
     */
    protected function _getNotifiedUnsupportedCountry()
    {
        if ($this->_lastNotifiedUnsupportedCountry === null) {
            $this->_lastNotifiedUnsupportedCountry = $this->_getLastNotifiedUnsupportedCountry();
        }

        return $this->_lastNotifiedUnsupportedCountry;
    }

    /**
     * @return string
     */
    protected function _getIpAddress()
    {
        /**
         * @var string ip Get IP from our custom method
         * because Kohana_Request can gets not really actual address but proxy
         */
        if ($this->_ip === null) {
            $this->_ip = F::getRealIpAddr();
        }

        return $this->_ip;
    }

    /**
     *
     */
    protected function _getCountryByIp()
    {
        if (Kohana::$environment >= Kohana::TESTING) {
            $this->_countryByIP = 'UA';
        }

        if ($this->_countryByIP === null) {
            $this->_countryByIP = F::getCountryByIP($this->_getIpAddress(), 'country_code');
        }

        return $this->_countryByIP;
    }

    /**
     * @param PopupData $popupData
     */
    protected function _addEventsShownPerPage(PopupData $popupData)
    {
        if (($eventShownPerPage = $popupData->getEvent()->getTypeId()) !== null) {
            $this->_eventsShownPerPage[] = $eventShownPerPage;
        }
    }

    /**
     * @param PopupData $popupData
     */
    protected function _addShownPopupsPerSession(PopupData $popupData)
    {
        if (
            ($popupId = $popupData->getId()) !== null
            && $popupData->isShowOnePerSession()
        ) {
            $eventId = $popupData->getEvent()->getId();

            $this->_shownPopupsPerSession[$eventId][] = $popupId;
        }
    }

    /**
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function _loadShownPopupsFromSession()
    {
        $shownPopups = Session::instance()->get('shownPopups');

        return ($shownPopups && is_array($shownPopups)) ? $shownPopups : [];
    }

    /**
     * @throws Exception
     */
    protected function _getLastNotifiedUnsupportedCountry()
    {
        if (isset($_COOKIE['lastNotifiedUnsupportedCountry'])) {
            return $this->_getLeadCookieValue(
                'lastNotifiedUnsupportedCountry',
                $_COOKIE['lastNotifiedUnsupportedCountry']
            );
        }

        return null;
    }

    /**
     * Function _getNonLeadIdByCookies
     * Find lead ID from cookies
     *
     * @return int|null
     * @throws Exception
     * @throws Throwable
     */
    protected function _loadLeadIdByCookies()
    {
        $leadId = null;

        $hashId = $this->_getLeadCookieValue(
            Kohana::config('leadAuthorizationCookiesHash.hashIdCookieName')
        );

        $hashSum = $this->_getLeadCookieValue(
            Kohana::config('leadAuthorizationCookiesHash.hashSumCookieName')
        );

        if (!$hashId || !$hashSum) {
            return $leadId;
        }

        try {
            /** @var object $lead AuthByCookie */
            $leadAuth = new AuthByCookie(
                $hashId,
                $hashSum,
                [],
                $this->_getReferrer(),
                $this->_getIpAddress()
            );

            /** Do not check last auth country and do not log this auth */
            $leadAuth->enablePartiallyLogin();

            $leadAuth->authenticate();

            $leadId = $leadAuth->getLeadId();
        } catch (AuthException $exception) {
            $leadId = null;
        }

        return $leadId;
    }

    /**
     * Function _getNonAuthIdByCookies
     * Find Non Auth ID from cookies
     *
     * @return null
     * @throws Exception
     * @throws Throwable
     */
    protected function _loadNonAuthIdByCookies()
    {
        $nonAuthId = null;

        if (!$this->_getLeadId()) {
            $nonAuthId = $this->_getLeadCookieValue(
                Kohana::config('leadAuthorizationCookiesHash.nonAuthIdCookiesName')
            ) ? : null;
        }

        return $nonAuthId;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function _detectLanguage()
    {
        $languagesIsoList = Model_Language::model()->getIsoList();

        /** @var string $language */
        $language = $_GET['lang'] ?? null;

        /** if we have some language value from url - and it is valid - use it */
        if ($language && in_array($language, $languagesIsoList)) {
            return $language;
        }

        $language = $this->_getLeadCookieValue(
            Kohana::config('leadAuthorizationCookiesHash.lastLanguageCookiesName')
        );

        /** if we have some language value from cookies - and it is valid - use it */
        if ($language && in_array($language, $languagesIsoList)) {
            return $language;
        }

        return Language::instance()->getDefault();
    }

    /**
     * Function _getLeadCookieValue
     *
     * @param null $default
     * @param      $cookieName
     *
     * @return mixed|null|string
     * @throws Exception
     *
     */
    protected function _getLeadCookieValue($cookieName, $default = null)
    {
        return ($cookieName)
            ? Cookie::get($cookieName, $default, Kohana::config('leadAuthorizationCookiesHash.salt'))
            : $default;
    }

    /**
     * @return RequestData
     *
     * @throws Throwable
     */
    protected function _getRequestData()
    {
        if ($this->_requestData === null) {
            $this->_requestData = new RequestData();

            $this->_fillRequestData($this->_requestData);
        }

        return $this->_requestData;
    }

    /**
     * @param RequestData $requestData
     *
     * @throws Exception
     * @throws Throwable
     */
    protected function _fillRequestData(RequestData $requestData)
    {
        $leadId = $this->_getLeadId();
        $countryByIP = $this->_getCountryByIp();
        $referrer = $this->_getReferrer();

        $requestData->leadId = $leadId;
        $requestData->lastNotifiedUnsupportedCountry = $this->_getNotifiedUnsupportedCountry();
        $requestData->shownEventsPerPage = $this->_getEventsShownPerPage();
        $requestData->shownPopupsPerSession = $this->_getShownPopupsPerSession();
        $requestData->countryByIP = $countryByIP;
        $requestData->calledFrom = $referrer;
        $requestData->lang = $this->_getLanguage();
        $requestData->notAuthUID = (!$leadId) ? $this->_getNonAuthId() : null;

        $callerPageData = $this->_getCallerPageData();
        $requestData->pageId = $callerPageData['id'] ?? null;
        $requestData->surveySubmitFailed = (bool)($callerPageData['submitFailed'] ?? null);
        $requestData->countryTypeOfReferer = $this->_getCountryTypeForLead($leadId, $referrer, $countryByIP);
    }

    /**
     * @param        $leadId
     * @param string $url
     * @param string $countryByIp
     *
     * @return mixed|null
     * @throws Exception
     */
    protected function _getCountryTypeForLead($leadId, $url, $countryByIp)
    {
        if ($leadId) {
            return Leads_CountryType::getLeadCountryType($leadId);
        }

        return Model_CountryType::getBySpecifiedCountries($url, $countryByIp);
    }
}
