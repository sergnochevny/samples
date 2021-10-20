<?php

namespace Popuper;

use Exception;
use Kohana_Cache_Exception;
use Kohana_Exception;
use Popuper\Events\EventInterface;
use Popuper\Helpers\Cache\Popups as PopupsCache;
use Popuper\Models\Assets as AssetsModel;
use Popuper\Models\EntityTypes as EntityTypesModel;
use Popuper\Models\Popups\Popups as PopupsModel;
use Popuper\Models\Popups\ShowTypes;

/**
 * Class PopupData
 *
  * @package Popuper\Repositories
 */
class PopupData
{
    /** @var int */
    protected $_id;

    /** @var string */
    protected $_name = '';

    /** @var bool */
    protected $_isActive = false;

    /** @var int */
    protected $_ignoreMobile = 0;

    /** @var int */
    protected $_showTypeId;

    /** @var array */
    protected $_assetsByTypes = [];

    /** @var string */
    protected $_htmlID;

    /** @var string */
    protected $_htmlClass;

    /** @var int */
    protected $_displaySettingId;

    /** @var EventInterface */
    protected $_event;

    /** @var array */
    protected $_variablesValues = [];

    /**
     * Function __construct
     * Popup constructor.
     *
     * @param int $popupId
     *
     * @throws Exception
     */
    public function __construct($popupId)
    {
        $this->_loadSelfData($popupId);
        $this->_loadAssetsByTypes();
    }

    /**
     * @return array
     */
    public function getGeneralOuterScripts()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_GENERAL_SCRIPT][AssetsModel::TOKEN_OUTER_ASSET]
            ??
            [];
    }

    /**
     * @return array
     */
    public function getGeneralInnerScripts()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_GENERAL_SCRIPT][AssetsModel::TOKEN_INNER_ASSET]
            ??
            [];
    }

    /**
     * @return array
     */
    public function getGeneralInnerStyles()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_GENERAL_STYLE][AssetsModel::TOKEN_INNER_ASSET]
            ??
            [];
    }

    /**
     * @return array
     */
    public function getGeneralOuterStyles()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_GENERAL_STYLE][AssetsModel::TOKEN_OUTER_ASSET]
            ??
            [];
    }

    /**
     * @return array
     */
    public function getCustomOuterScripts()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_SCRIPT][AssetsModel::TOKEN_OUTER_ASSET]
            ??
            [];
    }

    /**
     * @return array
     */
    public function getCustomInnerScripts()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_SCRIPT][AssetsModel::TOKEN_INNER_ASSET]
            ??
            [];
    }

    /**
     * @param array $customScripts
     */
    public function setCustomScripts(array $customScripts)
    {
        $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_SCRIPT] = $customScripts;
    }

    /**
     * @return array
     */
    public function getCustomOuterStyles()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_STYLE][AssetsModel::TOKEN_OUTER_ASSET]
            ??
            [];
    }

    /**
     * @return array
     */
    public function getCustomInnerStyles()
    {
        return $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_STYLE][AssetsModel::TOKEN_INNER_ASSET]
            ??
            [];
    }

    /**
     * @param array $customStyles
     */
    public function setCustomStyles($customStyles)
    {
        $this->_assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_STYLE] = $customStyles;
    }

    /**
     * @return integer
     */
    public function getDisplaySettingId()
    {
        return $this->_displaySettingId;
    }

    /**
     * _displaySettingId field's fluent setter
     *
          *
     * @param int $displaySettingId
     *
     * @return $this
     */
    public function setDisplaySettingId(int $displaySettingId): PopupData
    {
        $this->_displaySettingId = $displaySettingId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * _name field's fluent setter
     *
          *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): PopupData
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * Function isActive
     *
     *
          * @return bool
     */
    public function isActive()
    {
        return $this->_isActive;
    }

    /**
     * Function exists
     *
     *
          * @return bool
     */
    public function exists()
    {
        return (bool)$this->_id;
    }

    /**
     * @return string
     */
    public function getHtmlID()
    {
        return $this->_htmlID;
    }

    /**
     * _htmlID field's fluent setter
     *
          *
     * @param string $htmlID
     *
     * @return $this
     */
    public function setHtmlID(string $htmlID): PopupData
    {
        $this->_htmlID = $htmlID;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlClass()
    {
        return $this->_htmlClass;
    }

    /**
     * _htmlClass field's fluent setter
     *
          *
     * @param string $htmlClass
     *
     * @return $this
     */
    public function setHtmlClass(string $htmlClass): PopupData
    {
        $this->_htmlClass = $htmlClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * _id field's fluent setter
     *
          *
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id): PopupData
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * @return EventInterface
     */
    public function getEvent(): EventInterface
    {
        return $this->_event;
    }

    /**
     * @param EventInterface $event
     *
     * @return $this
     */
    public function setEvent(EventInterface $event): self
    {
        $this->_event = $event;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariablesValues(): array
    {
        return $this->_variablesValues;
    }

    /**
     * @param array $variablesValues
     *
     * @return PopupData
     */
    public function setVariablesValues(array $variablesValues)
    {
        $this->_variablesValues = $variablesValues;

        return $this;
    }

    /**
     * @return array
     */
    public function getAssetsByTypes(): array
    {
        return $this->_assetsByTypes;
    }

    /**
     * _isActive field's fluent setter
     *
          *
     * @param bool $isActive
     *
     * @return $this
     */
    public function setIsActive(bool $isActive): PopupData
    {
        $this->_isActive = $isActive;

        return $this;
    }

    /**
     * @param mixed $ignoreMobile
     *
     * @return PopupData
     */
    public function setIgnoreMobile($ignoreMobile)
    {
        $this->_ignoreMobile = $ignoreMobile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isIgnoreMobile()
    {
        return $this->_ignoreMobile;
    }

    /**
     * @return bool
     */
    public function isShowOnePerSession(): bool
    {
        //
        return $this->_showTypeId == ShowTypes::ID_ONCE_PER_SESSION;
    }

    /**
     * @param int $showType
     */
    public function setShowTypeId(int $showType)
    {
        $this->_showTypeId = $showType;
    }

    /**
     * @return int
     */
    public function getShowTypeId(): int
    {
        return $this->_showTypeId;
    }

    /**
     * @return array
     * @throws Kohana_Cache_Exception
     */
    protected function _loadAssetsByTypes()
    {
        if (!$this->_assetsByTypes) {
            if ($this->_id) {
                $cacheKeyName = $this->_id
                    . PopupsCache::PARTS_DELIMITER . 'PopupData'
                    . PopupsCache::PARTS_DELIMITER . 'Assets';

                $this->_assetsByTypes = PopupsCache::getByKey($cacheKeyName);
                if (!$this->_assetsByTypes) {
                    $this->_assetsByTypes = AssetsModel::getInstance()->getAllForPopup($this->_id);

                    PopupsCache::setByKey($cacheKeyName, $this->_assetsByTypes);
                }
            } else {
                $this->_assetsByTypes = AssetsModel::getInstance()->getAllForPopup(null);
            }
        }

        return $this->_assetsByTypes;
    }

    /**
     * Function _loadSelfData
     *
     *
          *
     * @param int $popupId
     *
     * @return void
     * @throws Kohana_Cache_Exception
     * @throws Kohana_Exception
     */
    protected function _loadSelfData($popupId)
    {
        $cacheKeyName = $popupId
            . PopupsCache::PARTS_DELIMITER . 'PopupData'
            . PopupsCache::PARTS_DELIMITER . 'Data';

        $popupData = PopupsCache::getByKey($cacheKeyName);
        if (!$popupData) {

            $popupData = PopupsModel::getInstance()->getById($popupId);

            PopupsCache::setByKey($cacheKeyName, $popupData);
        }

        $this->_id = $popupId;
        $this->_name = $popupData[PopupsModel::POPUP_NAME_FIELD] ?? '';
        $this->_htmlID = $popupData[PopupsModel::HTML_ID_FIELD] ?? '';
        $this->_htmlClass = $popupData[PopupsModel::HTML_CLASS_FIELD] ?? '';
        $this->_isActive = (bool)($popupData[PopupsModel::IS_ACTIVE_FIELD] ?? false);
        $this->_ignoreMobile = $popupData[PopupsModel::IGNORE_MOBILE_FIELD] ?? '0';
        $this->_displaySettingId = (int)($popupData[PopupsModel::DISPLAY_SETTING_ID_FIELD] ?? 0);
        $this->_showTypeId = (int)($popupData[PopupsModel::SHOW_TYPE_ID_FIELD] ?? 0);
    }

}