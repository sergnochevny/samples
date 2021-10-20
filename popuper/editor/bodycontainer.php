<?php

namespace Popuper\Editor;

use Exception;
use Kohana_Exception;
use Popuper\Models\Body as BodyModel;

/**
 *
  *
 * @package Popuper\Editor
 */
class BodyContainer
{
    /**
          *
     * @var
     */
    protected $_popupId;

    /**
          *
     * @var array
     */
    protected $_availableLanguages = [];

    /**
          *
     * @var array
     */
    protected $_contentByLanguages = [];

    /**
     * Function __construct
     * ContentContainer constructor.
     *
     * @param $_popupId
     *
     * @throws Exception
     */
    public function __construct($_popupId)
    {
        $this->_popupId = $_popupId;

        $this->load();
    }

    /**
          * @return array
     */
    public function getContentByLanguages()
    {
        return $this->_contentByLanguages;
    }

    /**
          *
     * @param array $contentByLanguages
     *
     */
    public function setContentByLanguages(array $contentByLanguages)
    {
        $this->_contentByLanguages = $contentByLanguages;
    }

    /**
     * Function save
     *
     *
          * @return bool
     * @throws Kohana_Exception
     */
    public function save()
    {
        return BodyModel::getInstance()
            ->setFewLanguagesForPopup(
                $this->_popupId,
                $this->_contentByLanguages
            );
    }

    /**
     * @param mixed $popupId
     *
     * @return BodyContainer
     */
    public function setPopupId($popupId)
    {
        $this->_popupId = $popupId;

        return $this;
    }

    /**
     */
    public function load()
    {
        $contents = BodyModel::getInstance()
            ->getAllForPopup($this->_popupId);

        $this->setContentByLanguages($contents);
    }

}