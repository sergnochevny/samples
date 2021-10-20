<?php

namespace Controller;

use Exception;
use Popuper\PopupsManager;
use Popuper\Renderer\Combiner as PopupRenderer;
use Popuper\Renderer\RenderingData;

/**
 * Trait GetRenderingData
 *
 * @package Controller
 */
trait GetRenderingData
{
    use CollectParameters;

    /**
     * @var PopupsManager
     */
    protected $_popupsManager;

    /**
     * @var PopupRenderer
     */
    protected $_popupRenderer;

    /**
     * @return RenderingData
     * @throws Exception
     */
    protected function _getRenderingData(): RenderingData
    {
        $requestData = $this->_getRequestData();

        $popupRenderer = $this->_getPopupRenderer();

        $popupsManager = $this->_getPopupsManager()
            ->setRequestData($requestData);

        if ($popupData = $popupsManager->getPopupData()) {
            $popupRenderer->reset()
                ->setLanguage($requestData->lang)
                ->setPopupData($popupData);
        }

        return $popupRenderer->getData();
    }

    /**
     * @return PopupRenderer
     */
    protected function _getPopupRenderer(): PopupRenderer
    {
        if ($this->_popupRenderer === null) {
            $this->_popupRenderer = new PopupRenderer();
        }

        return $this->_popupRenderer;
    }

    /**
     * @return PopupsManager
     */
    protected function _getPopupsManager()
    {
        if ($this->_popupsManager === null) {
            $this->_popupsManager = new PopupsManager();
        }

        return $this->_popupsManager;
    }

}