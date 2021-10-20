<?php

use Controller\GetRenderingData as GetRenderingDataTrait;
use Controller\JSTemplateAbstract;

/**
 * Class Controller_Popups
 * class of general pop-up calls
 *
 */
class Controller_Popups extends JSTemplateAbstract
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
        $this->template->renderingData = $this->_getRenderingData();
    }

}
