<?php

use Controller\GetRenderingData as GetRenderingDataTrait;
use Controller\JsonTemplateAbstract;

/**
 * Class Controller_NextPopup
 *
 * Controller with actions which called from js popups-manager
 * for getting next popup
 */
class Controller_NextPopup extends JsonTemplateAbstract
{
    use GetRenderingDataTrait;

    /**
     * @throws Kohana_Exception
     * @throws Exception
     */
    public function after()
    {
        $popupsManager = $this->_getPopupsManager();

        if ($popupData = $popupsManager->getPopupData()) {
            $this->_addEventsShownPerPage($popupData);
            $this->_addShownPopupsPerSession($popupData);
        }

        parent::after();

        if ($popupData) {
            $popupsManager->markPopupAsShown($popupData);
        }
    }

    /**
     * Function action_get
     *
     * @throws Exception
     */
    public function action_get()
    {
        $renderingData = $this->_getRenderingData();

        $this->template->result = $renderingData->toArray();
    }

}