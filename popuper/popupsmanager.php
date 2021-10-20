<?php

namespace Popuper;

use Exception;
use Kohana_Exception;
use Popuper\Conditions\Identifier as PopupIdentifier;
use Popuper\Events\EventInterface;
use Popuper\Events\Rules\Checker as RulesChecker;
use Popuper\EventsManager as EventsManager;
use Popuper\Models\Events\Variables as EventVariablesModel;
use Popuper\Models\Popups\ForEventTypes;
use Popuper\Models\Popups\Popups as PopupsModel;
use Popuper\Variables\Dynamic\DynamicData;
use Popuper\Variables\RequestData;
use SystemOptions;

/**
 * Class PopupsManager
 *
  * @package Popuper
 */
class PopupsManager
{
    /** @var EventsManager */
    protected $_eventsManager;

    /** @var RequestData */
    protected $_requestData;

    /** @var PopupData */
    protected $_popupData;

    /** @var bool */
    protected $_isLoaded = false;

    /** @var RulesChecker */
    protected $_rulesChecker;

    /**
     * @return bool
     * @throws Exception
     */
    public static function isEnabled()
    {
        return SystemOptions::get('Pop-uper//Pop-uper Enabled');
    }

    /**
     * @return PopupData
     * @throws Exception
     */
    public function getPopupData()
    {
        if (!$this->_requestData) {
            return null;
        }

        if (!$this->_isLoaded) {
            $this->_popupData = $this->_getEventValidPopupData();
            $this->_isLoaded = true;
        }

        return $this->_popupData;
    }

    /**
     * @param RequestData $requestData
     *
     * @return PopupsManager
     */
    public function setRequestData(RequestData $requestData): self
    {
        $this->_requestData = $requestData;

        return $this;
    }

    /**
     * @param PopupData $popup
     *
     * @return bool
     * @throws Kohana_Exception
     */
    public function markPopupAsShown(PopupData $popup): bool
    {
        $nextPopupId = $this->_getNextPopupId($popup);

        return $this->_getEventsManager()
            ->markEventAsHandledByPopup($popup, !is_null($nextPopupId));
    }

    /**
     * @return PopupData
     * @throws Exception
     */
    protected function _getEventValidPopupData()
    {
        if (!static::isEnabled()) {
            return null;
        }

        $eventsManager = $this->_getEventsManager();

        foreach ($this->_getLeadIds() as $leadId) {
            $this->_requestData->leadId = $leadId;

            $leadEnabledEvents = $eventsManager->getLeadEnabledEvents(
                $this->_requestData->leadId,
                $this->_requestData->excludedTypes
            );

            /** @var EventInterface $event */
            foreach ($leadEnabledEvents as $event) {
                if ($popupData = $this->_loadEventPopupData($event)) {
                    return $popupData;
                } else {
                    $this->_removeEventWithoutPopups($event);
                }
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function _getLeadIds(): array
    {
        if (!$this->_requestData->leadId) {
            $leadIds = array_unique([$this->_requestData->notAuthUID, null]);
        } else {
            $leadIds = [$this->_requestData->leadId];
        }

        return $leadIds;
    }

    /**
     * @return EventsManager
     */
    protected function _getEventsManager(): EventsManager
    {
        if ($this->_eventsManager === null) {
            $this->_eventsManager = new EventsManager();
        }

        return $this->_eventsManager;
    }

    /**
     * @param EventInterface $event
     *
     * @return PopupData
     * @throws Exception
     */
    protected function _loadEventPopupData(EventInterface $event)
    {
        $variablesRepository = $this->_getEventsManager()
            ->getVariablesRepository()
            ->setRequestData($this->_requestData);

        $variablesValues = $variablesRepository->getVariablesValues();

        if (!($popupId = $this->_findEventPopupId($event, $variablesValues))) {
            return null;
        }

        $popupVariables = $variablesRepository->getVariablesValues(true);

        return (new PopupData($popupId))
            ->setEvent($event)
            ->setVariablesValues($popupVariables);
    }

    /**
     * @param EventInterface $event
     * @param array          $variablesValues
     *
     * @return int|null
     * @throws Kohana_Exception
     */
    protected function _findEventPopupId(EventInterface $event, array $variablesValues)
    {
        if (!$this->_checkEventToShowPopups($event, $variablesValues)) {
            return null;
        }

        if ($this->_hasPopupsInVariables($variablesValues)) {
            return $this->_getPopupIdFromVariables($variablesValues);
        }

        return PopupIdentifier::getPopupIdByEventType($event->getTypeId(), $variablesValues);
    }

    /**
     * @param EventInterface $event
     * @param array          $variablesValues
     *
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _checkEventToShowPopups(EventInterface $event, array $variablesValues)
    {
        return $this->_checkGeneralRulesShowPopup($event, $variablesValues)
            && $event->isAllowedToShowPopups($variablesValues);
    }

    /**
     * @param EventInterface $event
     * @param array          $variablesValues
     *
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _checkGeneralRulesShowPopup(EventInterface $event, array $variablesValues)
    {
        $rulesChecker = $this->_loadRulesChecker();

        return $rulesChecker->check($event->getTypeId(), $variablesValues);
    }

    /**
     * @return RulesChecker
     */
    protected function _loadRulesChecker(): RulesChecker
    {
        if ($this->_rulesChecker === null) {
            $this->_rulesChecker = new RulesChecker();
        }

        return $this->_rulesChecker;
    }

    /**
     * @param array $variablesValues
     *
     * @return bool
     */
    protected function _hasPopupsInVariables(array $variablesValues)
    {
        return ($variablesValues[EventVariablesModel::PREVIEW_POPUP_IDS_LIST] ?? [])
            || ($variablesValues[EventVariablesModel::CUSTOM_POPUP_IDS_LIST] ?? []);
    }

    /**
     * @param array $variablesValues
     *
     * @return int|null
     * @throws Kohana_Exception
     */
    protected function _getPopupIdFromVariables(array $variablesValues)
    {
        /** get popup for preview */
        $popupsIds = $this->_getPreviewPopupsIds($variablesValues);
        if ($popupsIds) {
            return array_shift($popupsIds);
        }

        /** get popup from custom popups list */
        $popupsIds = $this->_getCustomPopupsIds($variablesValues);
        if ($popupsIds) {
            return array_shift($popupsIds);
        }

        return null;
    }

    /**
          *
     * @param array $variablesValues
     *
     * @return array
     * @throws Kohana_Exception
     */
    protected function _getCustomPopupsIds(array $variablesValues): array
    {
        $excludedPopupsIds = $variablesValues[DynamicData::EXCLUDED_POPUP_IDS] ?? [];
        $customPopupsIds = array_diff(
            $variablesValues[EventVariablesModel::CUSTOM_POPUP_IDS_LIST] ?? [],
            $excludedPopupsIds
        );

        if (empty($customPopupsIds)) {
            return [];
        }

        /**
         * ignore parameter by set to null when not mobile
         */
        $forMobile = $variablesValues[DynamicData::FOR_MOBILE] ?? false;
        $ignoreMobile = $forMobile ? !$forMobile : null;

        /**
         * Validate custom popups on isActive = 1 and ignoreMobile
         */
        $customPopups = PopupsModel::getInstance()
            ->getByIdsArray($customPopupsIds, true, $ignoreMobile);

        return array_keys($customPopups);
    }

    /**
          *
     * @param array $variablesValues
     *
     * @return array
     */
    protected function _getPreviewPopupsIds(array $variablesValues): array
    {
        $excludedPopupsIds = $variablesValues[DynamicData::EXCLUDED_POPUP_IDS] ?? [];
        $previewPopupsIds = $variablesValues[EventVariablesModel::PREVIEW_POPUP_IDS_LIST] ?? [];

        if (empty($previewPopupsIds)) {
            return [];
        }
        $previewPopupsIds = (!is_array($previewPopupsIds)) ? [$previewPopupsIds] : $previewPopupsIds;

        $previewPopupsIds = array_diff($previewPopupsIds, $excludedPopupsIds);

        if (empty($previewPopupsIds)) {
            return [];
        }

        return $previewPopupsIds;
    }

    /**
     * @param PopupData $popup
     *
     * @return int|null
     *
     * @throws Kohana_Exception
     */
    protected function _getNextPopupId(PopupData $popup)
    {
        $variablesRepository = $this->_getEventsManager()
            ->getVariablesRepository()
            ->setRequestData($this->_requestData);

        $variables = $variablesRepository->getVariablesValues();
        $excludedPopups = $variables[DynamicData::EXCLUDED_POPUP_IDS] ?? [];
        $excludedPopups[] = $popup->getId();
        $variables[DynamicData::EXCLUDED_POPUP_IDS] = array_unique($excludedPopups);
        $nextPopupId = $this->_findEventPopupId($popup->getEvent(), $variables);

        return $nextPopupId;
    }

    /**
     * Remove event if all popups are disabled
     *
     * @param EventInterface $event
     *
     * @return void
     * @throws Kohana_Exception
     */
    protected function _removeEventWithoutPopups(EventInterface $event)
    {
        if (!$event->canBeRemovedOnPopupShow(false)) {
            return;
        }

        $activePopups = ForEventTypes::getInstance()
            ->getPopupsByEvent($event->getTypeId(), true);
        if (!$activePopups) {
            $this->_getEventsManager()
                ->removeEvent($event, 'No popups to show');
        }

    }

}