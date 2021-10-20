<?php

namespace Popuper\Editor;

use Arr;
use Database_TransactionException;
use DB;
use Exception;
use Kohana_Exception;
use Language;
use Leads\Request\Types as LeadsRequestTypesModel;
use Model_Language;
use Popuper\Conditions\Identifier as ConditionsIdentifier;
use Popuper\Conditions\Maps\ConditionViewFields as ConditionViewFieldsMap;
use Popuper\Conditions\Providers\ConditionsFields;
use Popuper\Conditions\Saver as PopupsConditionsSaver;
use Popuper\Editor\Templates\Exceptions\InvalidEntityTypeException;
use Popuper\Editor\Templates\Manager as TemplatesManager;
use Popuper\Editor\Validators\EditPopup as EditPopupValidator;
use Popuper\Helpers\Cache\Popups as PopupsCache;
use Popuper\Helpers\DataKeys;
use Popuper\Helpers\ErrorFormatter;
use Popuper\Helpers\PrepareData as PrepareDataHelper;
use Popuper\Helpers\ValuesDecorator;
use Popuper\Models\Assets as AssetsModel;
use Popuper\Models\BodyRevisions as PopupsBodyRevisionsModel;
use Popuper\Models\DisplaySettings as DisplaySettingsModel;
use Popuper\Models\EntityTypes as EntityTypesModel;
use Popuper\Models\Events\Types as EventTypesModel;
use Popuper\Models\PopuperRevisions as PopuperRevisionsModel;
use Popuper\Models\Popups\ForEventTypes as PopupsForEventTypesModel;
use Popuper\Models\Popups\ForEventTypesRevisions as PopupsForEventTypesRevisionsModel;
use Popuper\Models\Popups\Popups as PopupsModel;
use Popuper\Models\Popups\Revisions as PopupsRevisionsModel;
use Popuper\Models\Popups\ShowTypes as ShowTypesModel;
use Popuper\Models\Templates\TemplatesValuesRevisions as PopupValuesRevisionsModel;
use Popuper\PopupData;
use Popuper\Repositories\Conditions as ConditionsRepository;
use Popuper\Repositories\DefaultValues as DefaultValuesRepository;
use Popuper\Repositories\Values as PopupValuesRepository;
use Popuper\Repositories\Variables as VariablesRepository;
use Popuper\Variables\RequestData;
use ReflectionException;

/**
 * Class Popup
 *
 * @package Popuper\Editor
 */
class Popup
{
    /** @var array */
    const FIELDS_FOR_BASE_EDIT = [
        DataKeys::HTML_ID_KEY => '',
        DataKeys::POPUP_NAME_KEY => '',
        DataKeys::DISPLAY_SETTINGS_KEY => 0,
        DataKeys::HTML_CLASS_KEY => '',
        DataKeys::BODY_KEY => [],
        DataKeys::STYLES_KEY => [],
        DataKeys::SCRIPTS_KEY => [],
        DataKeys::CONDITIONS_DATA_KEY => [],
        DataKeys::POPUP_TYPES_KEY => [],
        DataKeys::LANGUAGES_KEY => 0,
        DataKeys::EVENT_TYPE_ID_KEY => 0,
        DataKeys::ORDER_FOR_EVENT_KEY => 0,
        DataKeys::IGNORE_MOBILE_KEY => '0',
        DataKeys::DISPLAY_TYPES_KEY => 0,
    ];

    /** @var array */
    const FIELDS_FOR_LIST_POSITION_CHANGE = [
        DataKeys::IS_ACTIVE_KEY => false,
        DataKeys::ORDER_FOR_EVENT_KEY => 0,
    ];

    /** @var array */
    const BASE_FIELDS_KEYS_MAP = [
        DataKeys::HTML_CLASS_KEY => PopupsModel::HTML_CLASS_FIELD,
        DataKeys::DISPLAY_SETTINGS_KEY => PopupsModel::DISPLAY_SETTING_ID_FIELD,
        DataKeys::IS_ACTIVE_KEY => PopupsModel::IS_ACTIVE_FIELD,
        DataKeys::POPUP_NAME_KEY => PopupsModel::POPUP_NAME_FIELD,
        DataKeys::HTML_ID_KEY => PopupsModel::HTML_ID_FIELD,
        DataKeys::IGNORE_MOBILE_KEY => PopupsModel::IGNORE_MOBILE_FIELD,
        DataKeys::DISPLAY_TYPES_KEY => PopupsModel::SHOW_TYPE_ID_FIELD,
    ];

    /** @var int */
    protected $_eventTypeId = 0;

    /** @var bool */
    protected $_loaded = false;

    /** @var PopupData|null */
    protected $_popupData;

    /** @var int */
    protected $_orderForEvent = null;

    /** @var BodyContainer */
    protected $_bodyContainer;

    /** @var array */
    protected $_validationErrors = [];

    /** @var int */
    protected $_defaultEventId = EventTypesModel::EVENT_UNSPECIFIED_ONE_TIME;

    /** @var array|null */
    protected $_conditionsData;

    /** @var array|null */
    protected $_templateValues;

    /** @var array */
    protected $_templatesValues;

    /** @var int */
    protected $_revisionId;

    /** @var array */
    protected $_editableFields = [];

    /**
     * @param $eventTypeId
     * @param $popupId
     *
     * @return Popup
     *
     * @throws Exception
     */
    public static function getBaseEditor($eventTypeId, $popupId): Popup
    {
        return new static ($eventTypeId, $popupId, static::FIELDS_FOR_BASE_EDIT);
    }

    /**
     * @param $eventTypeId
     * @param $popupId
     *
     * @return Popup
     *
     * @throws Exception
     */
    public static function getListPositionEditor($eventTypeId, $popupId): Popup
    {
        return new static ($eventTypeId, $popupId, static::FIELDS_FOR_LIST_POSITION_CHANGE);
    }

    /**
     * Function __construct
     * Single constructor.
     *
     * @param       $eventTypeId
     * @param       $popupId
     *
     * @param array $allowEditFields
     *
     * @throws Exception
     */
    public function __construct($eventTypeId, $popupId, array $allowEditFields = [])
    {
        $this->_eventTypeId = $eventTypeId;
        $this->_editableFields = $allowEditFields;

        $this->_loadById($popupId);
    }

    /**
     * Get popupId
     *
     * @return int
     */
    public function getPopupId()
    {
        return $this->_popupData->getId();
    }

    /**
     * @return int
     */
    public function getEventTypeId()
    {
        return $this->_eventTypeId;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setEventTypeId(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::EVENT_TYPE_ID_KEY)) {
            return;
        }

        $newEventId = $data[DataKeys::EVENT_TYPE_ID_KEY] ?? null;

        if (!$newEventId && !$this->_eventTypeId) {
            $newEventId = $this->_defaultEventId;
        }

        if ($newEventId) {
            $this->_eventTypeId = $newEventId;
        }
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->_validationErrors;
    }

    /**
     * Function setData
     *
     * @param array $postData
     *
     * @return $this
     */
    public function setData(array $postData)
    {
        //filtering incoming data by valid types
        $data = $this->_filterIncomingData($postData);

        $this->_setConditions($data);
        $this->_setBody($data);
        $this->_setHtmlID($data);
        $this->_setHtmlClass($data);
        $this->_setEventTypeId($data);
        $this->_setIsActive($data);
        $this->_setOrderForEvent($data);
        $this->_setCustomStyles($data);
        $this->_setCustomScripts($data);
        $this->_setTemplateValues($data);
        $this->_setName($data);
        $this->_setDisplaySettingId($data);
        $this->_setIgnoreMobile($data);
        $this->_setShowType($data);

        return $this;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setTemplateValues(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::POPUP_TYPES_KEY)) {
            return;
        }

        $values = ValuesDecorator::normalize(
            $data[DataKeys::POPUP_TYPES_KEY] ?? []
        );

        // trim trailing spaces for other data
        $this->_templateValues = PrepareDataHelper::filterByEmptyValues($values);
    }

    /**
     * @return array
     * @throws Kohana_Exception
     * @throws Exception
     */
    public function getFormData()
    {
        $templatesManager = $this->_loadTemplatesManager();

        return [
            DataKeys::POPUP_KEY => $this->_preparePopupFormData(
                $templatesManager->getTemplateData()
            ),
            DataKeys::POPUP_DATA_KEY => $this->_preparePopupFormValues(
                $templatesManager->getValuesData()
            ),
        ];
    }

    /**
     * Function save
     *
     * @return bool
     * @throws Database_TransactionException
     * @throws InvalidEntityTypeException
     * @throws Kohana_Exception
     * @throws ReflectionException
     * @throws Exception
     */
    public function save()
    {
        if ($this->_isFieldsEditable(DataKeys::POPUP_TYPES_KEY)) {
            if (!$this->_validateTemplate()) {
                return false;
            }
        }

        if (!$this->_saveCurrentState()) {
            $this->_validationErrors[] = 'Popup not saved!';

            return false;
        }

        /** Sync current entity with data from DB */
        $this->_loadById(
            $this->_popupData->getId()
        );

        return true;
    }

    /**
     * @param int $revisionId
     *
     * @return Popup
     */
    public function setRevisionId(int $revisionId): self
    {
        $this->_revisionId = $revisionId;

        return $this;
    }

    /**
     * @return bool
     * @throws Database_TransactionException
     * @throws Exception
     */
    protected function _saveCurrentState()
    {
        DB::startTransaction();

        try {
            if (!$this->_revisionId) {
                $this->setRevisionId(PopuperRevisionsModel::getInstance()->initNew());
            }

            $eventRelationUpdated = false;
            $templatesValuesUpdated = false;
            $contentUpdated = false;
            $assetsUpdated = false;
            $conditionsUpdated = false;

            $popupsUpdated = $this->_saveBaseData();

            if ($this->_isFieldsEditable(DataKeys::ORDER_FOR_EVENT_KEY)) {
                $eventRelationUpdated = $this->_saveEventTypesRelation();
            }

            if ($this->_isFieldsEditable(DataKeys::POPUP_TYPES_KEY)) {
                $templatesValuesUpdated = $this->_saveTemplatesValues();
            }

            if ($this->_isFieldsEditable(DataKeys::BODY_KEY)) {
                $contentUpdated = $this->_saveContent();
            }

            if (
                $this->_isFieldsEditable(DataKeys::STYLES_KEY)
                || $this->_isFieldsEditable(DataKeys::SCRIPTS_KEY)
            ) {
                $assetsUpdated = $this->_saveAssets();
            }

            if ($this->_isFieldsEditable(DataKeys::CONDITIONS_DATA_KEY)) {
                $conditionsUpdated = $this->_saveConditions();
            }

            $result = $popupsUpdated
                || $templatesValuesUpdated
                || $contentUpdated
                || $eventRelationUpdated
                || $assetsUpdated
                || $conditionsUpdated;

            if ($result) {
                PopupsCache::removeAllByPrefix($this->_popupData->getId());

                DB::commit();
            } else {
                DB::rollback();
            }
        } catch (Exception $exception) {
            DB::rollback();

            throw $exception;
        }

        return $result;
    }

    /**
     * @param $popupId
     *
     * @return Popup
     * @throws Exception
     */
    protected function _loadById($popupId): self
    {
        $this->_popupData = new PopupData($popupId);

        $eventRelationData = PopupsForEventTypesModel::getInstance()->getEventTypeByPopup($popupId);

        if ($eventRelationData) {
            $this->_eventTypeId = $eventRelationData['eventTypeId'] ?? null;
        }

        if (!$this->_eventTypeId) {
            $this->_eventTypeId = $this->_defaultEventId;
        }

        $this->_orderForEvent = $eventRelationData['order'] ?? null;
        $this->_bodyContainer = new BodyContainer($popupId);

        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getCombinedStateData()
    {
        $filteredData = [DataKeys::BODY_KEY => $this->_bodyContainer->getContentByLanguages()];

        $filteredData += [
            DataKeys::POPUP_TYPES_KEY => $this->_templateValues,
            DataKeys::LANGUAGES_KEY => PrepareDataHelper::getLanguagesByValues(
                $filteredData + $this->_templateValues
            ),
            DataKeys::STYLES_KEY => array_merge(
                $this->_popupData->getCustomOuterStyles(),
                $this->_popupData->getCustomInnerStyles()
            ),
            DataKeys::SCRIPTS_KEY => array_merge(
                $this->_popupData->getCustomOuterScripts(),
                $this->_popupData->getCustomInnerScripts()
            ),
            DataKeys::EVENT_TYPE_ID_KEY => $this->getEventTypeId(),
            DataKeys::CONDITIONS_DATA_KEY => $this->_conditionsData,
        ];

        // popup parameters data (all from popupsModel)
        $filteredData += $this->_getFilteredParametersData();

        return $filteredData;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _getFilteredParametersData()
    {
        return [
            DataKeys::POPUP_NAME_KEY => $this->_popupData->getName(),
            DataKeys::DISPLAY_SETTINGS_KEY => $this->_popupData->getDisplaySettingId(),
            DataKeys::HTML_ID_KEY => $this->_popupData->getHtmlID(),
            DataKeys::HTML_CLASS_KEY => $this->_popupData->getHtmlClass(),
            DataKeys::IS_ACTIVE_KEY => $this->_popupData->isActive(),
            DataKeys::IGNORE_MOBILE_KEY => $this->_popupData->isIgnoreMobile(),
            DataKeys::DISPLAY_TYPES_KEY => $this->_popupData->getShowTypeId(),
        ];
    }

    /**
     * it prepare popup values data for the edit form
     *
     * @param array $popupValues
     *
     * @return array
     * @throws Kohana_Exception
     * @throws Exception
     */
    protected function _preparePopupFormValues(array $popupValues)
    {
        $popupValuesData = [
            DataKeys::POPUP_TYPES_KEY => $popupValues,
            DataKeys::BODY_KEY => $this->_bodyContainer->getContentByLanguages(),
            DataKeys::POPUP_NAME_KEY => $this->_popupData->getName(),
            DataKeys::HTML_ID_KEY => $this->_popupData->getHtmlID(),
            DataKeys::HTML_CLASS_KEY => $this->_popupData->getHtmlClass(),
            DataKeys::STYLES_KEY => array_values(
                array_merge(
                    $this->_popupData->getCustomOuterStyles(),
                    $this->_popupData->getCustomInnerStyles()
                )
            ),
            DataKeys::SCRIPTS_KEY => array_values(
                array_merge(
                    $this->_popupData->getCustomOuterScripts(),
                    $this->_popupData->getCustomInnerScripts()
                )
            ),
            DataKeys::IS_ACTIVE_KEY => $this->_popupData->isActive(),
            DataKeys::DISPLAY_SETTINGS_KEY => $this->_popupData->getDisplaySettingId(),
            DataKeys::IGNORE_MOBILE_KEY => $this->_popupData->isIgnoreMobile(),
            DataKeys::DISPLAY_TYPES_KEY => $this->_popupData->getShowTypeId(),
        ];

        if (empty($this->_conditionsData) && !empty($this->_popupData->getId())) {
            $this->_conditionsData = ConditionsIdentifier::getPopupConditionsData($this->_popupData->getId());
        }

        $popupValuesData += [
            DataKeys::EVENT_TYPE_KEY => EventTypesModel::getInstance()->getById($this->_eventTypeId),
            DataKeys::DEFAULT_LANG_KEY => Language::instance()->getDefault(),
            DataKeys::CONDITIONS_DATA_KEY => $this->_conditionsData,
        ];

        return $popupValuesData;
    }

    /**
     * it prepare popup form data (references & etc) for the edit form
     *
     * @param $popupFormData
     *
     * @return array
     * @throws Exception
     */
    protected function _preparePopupFormData($popupFormData)
    {
        $requestData = new RequestData();

        $popupFormData[DataKeys::AVAILABLE_VARIABLES_KEY] = (new VariablesRepository())
            ->setRequestData($requestData)
            ->setEventTypeId($this->_eventTypeId)
            ->getFormattedListForEditor();

        $popupFormData[DataKeys::CONDITIONS_KEY] = [
            DataKeys::LOGICAL_OPERATORS_KEY => ConditionsRepository::getAllLogicOperators(),
            DataKeys::COMPARISON_OPERATORS_KEY => ConditionsRepository::getAllConditionsOperators(),
            DataKeys::FIELDS_KEY => (!empty($this->_popupData->getId()))
                ? ConditionsFields::getInstance(true)->getFieldsDataByPopup($this->_popupData->getId())
                : ConditionsFields::getInstance(true)->getFieldsDataByEventType($this->_eventTypeId),
        ];

        $popupFormData[DataKeys::STYLES_KEY] = array_merge(
            $this->_popupData->getGeneralOuterStyles(),
            $this->_popupData->getGeneralInnerStyles()
        );

        $popupFormData[DataKeys::SCRIPTS_KEY] = array_merge(
            $this->_popupData->getGeneralOuterScripts(),
            $this->_popupData->getGeneralInnerScripts()
        );

        $popupFormData[DataKeys::LANGUAGES_KEY] = PrepareDataHelper::formatFlagsAssets(
            Model_Language::model()->getListForPopupEditor()
        );

        $popupFormData[DataKeys::DISPLAY_TYPES_KEY] = [
            DataKeys::LIST_KEY => array_column(
                ShowTypesModel::getInstance()
                    ->getForEvent($this->_eventTypeId),
                'name',
                'id'
            ),
        ];

        $popupFormData[DataKeys::DISPLAY_SETTINGS_KEY] = [
            DataKeys::LIST_KEY => array_column(
                DisplaySettingsModel::getInstance()->getAll(),
                'name',
                'id'
            ),
        ];

        $popupFormData[DataKeys::LEAD_REQUEST_TYPES] = LeadsRequestTypesModel::getItems();

        $popupFormData[DataKeys::DEFAULT_VALUES] = ValuesDecorator::denormalize(
            (new DefaultValuesRepository($this->_eventTypeId))->getCollection()
        );

        return $popupFormData;
    }

    /**
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _saveBaseData()
    {
        if (!$this->_revisionId) {
            return false;
        }

        $newData = $this->_combineBaseDataToSave();

        if (!$newData) {
            return false;
        }

        $popupStoreModel = PopupsModel::getInstance();

        $oldData = $popupStoreModel->getByIdOnlyAllowedFields(
            $this->_popupData->getId()
        );

        $savedForId = $popupStoreModel->save(
            $this->_popupData->getId(),
            $newData
        );

        if ($savedForId) {
            $this->_popupData->setId($savedForId);
        }

        return $savedForId
            && PopupsRevisionsModel::getInstance()
                ->save(
                    $this->_popupData->getId(),
                    $this->_revisionId,
                    Arr::diffRecursive($newData, $oldData)
                );
    }

    /**
     * @return bool
     * @throws Kohana_Exception
     * @throws Exception
     */
    protected function _saveTemplatesValues()
    {
        if (!$this->_revisionId || !$this->_popupData->getId()) {
            return false;
        }

        $popupValuesUpdated = (new PopupValuesRepository($this->_popupData->getId()))
            ->saveCollection($this->_templatesValues);

        $popupValuesUpdated = $popupValuesUpdated
            && PopupValuesRevisionsModel::getInstance()->save(
                $this->_revisionId,
                $this->_templatesValues
            );

        return $popupValuesUpdated;
    }

    /**
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _saveEventTypesRelation()
    {
        if (!$this->_revisionId || !$this->_popupData->getId()) {
            return false;
        }

        $eventRelationUpdated = PopupsForEventTypesModel::getInstance()
            ->setPopupForEvent(
                $this->_popupData->getId(),
                $this->_eventTypeId,
                $this->_orderForEvent
            );

        $eventRelationUpdated = $eventRelationUpdated
            && PopupsForEventTypesRevisionsModel::getInstance()
                ->save(
                    $this->_revisionId,
                    $this->_popupData->getId(),
                    $this->_eventTypeId,
                    $this->_orderForEvent
                );

        return $eventRelationUpdated;
    }

    /**
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _saveContent()
    {
        if (!$this->_revisionId || !$this->_popupData->getId()) {
            return false;
        }

        $this->_bodyContainer->setPopupId(
            $this->_popupData->getId()
        );

        return $this->_bodyContainer->save()
            && PopupsBodyRevisionsModel::getInstance()
                ->save(
                    $this->_revisionId,
                    $this->_bodyContainer->getContentByLanguages()
                );
    }

    /**
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _saveAssets()
    {
        if (!$this->_revisionId || !$this->_popupData->getId()) {
            return false;
        }

        $assetsByTypes = $this->_getAssetsToSave();

        return AssetsModel::getInstance()
            ->saveForPopup(
                $this->_popupData->getId(),
                $assetsByTypes,
                $this->_revisionId
            );
    }

    /**
     * @return mixed
     * @throws Database_TransactionException
     * @throws Kohana_Exception
     */
    protected function _saveConditions()
    {
        return PopupsConditionsSaver::save($this->_popupData->getId(), $this->_conditionsData);
    }

    /**
     * @return TemplatesManager
     */
    protected function _loadTemplatesManager(): TemplatesManager
    {
        return new TemplatesManager($this->_eventTypeId, $this->_popupData->getId());
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setHtmlID(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::HTML_ID_KEY)) {
            return;
        }

        // trim trailing spaces for other data
        $this->_popupData->setHtmlID(
            PrepareDataHelper::filterByEmptyValues($data[DataKeys::HTML_ID_KEY] ?? '')
        );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setHtmlClass(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::HTML_CLASS_KEY)) {
            return;
        }

        // trim trailing spaces for other data
        $this->_popupData->setHtmlClass(
            PrepareDataHelper::filterByEmptyValues($data[DataKeys::HTML_CLASS_KEY] ?? '')
        );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setBody(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::BODY_KEY)) {
            return;
        }

        // trim trailing spaces for other data
        $this->_bodyContainer->setContentByLanguages(
            PrepareDataHelper::filterByEmptyValues($data[DataKeys::BODY_KEY] ?? [])
        );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setConditions(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::CONDITIONS_DATA_KEY)) {
            return;
        }
        $this->_conditionsData = $data[DataKeys::CONDITIONS_DATA_KEY] ?? [];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setIsActive(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::IS_ACTIVE_KEY)) {
            return;
        }
        if (!is_null($newIsActive = $data[DataKeys::IS_ACTIVE_KEY] ?? null)) {
            $this->_popupData->setIsActive($newIsActive);
        }
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setOrderForEvent(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::ORDER_FOR_EVENT_KEY)) {
            return;
        }

        if ($newOrderForEvent = ($data[DataKeys::ORDER_FOR_EVENT_KEY] ?? null)) {
            $this->_orderForEvent = $newOrderForEvent;
        }

        if (!$this->_orderForEvent && $this->_eventTypeId) {
            $this->_orderForEvent = (int) PopupsForEventTypesModel::getInstance()
                    ->getMaxOrderForEvent($this->_eventTypeId) + 1;
        }
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setCustomStyles(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::STYLES_KEY)) {
            return;
        }

        $styles = $data[DataKeys::STYLES_KEY] ?? [];

        if ($styles && is_array($styles)) {
            $styles = array_unique($styles);
        }

        // trim trailing spaces for other data
        $this->_popupData->setCustomStyles(
            [AssetsModel::TOKEN_OUTER_ASSET => PrepareDataHelper::filterByEmptyValues($styles)]
        );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setCustomScripts(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::SCRIPTS_KEY)) {
            return;
        }

        $scripts = $data[DataKeys::SCRIPTS_KEY] ?? [];

        if ($scripts && is_array($scripts)) {
            $scripts = array_unique($scripts);
        }

        // trim trailing spaces for other data
        $this->_popupData->setCustomScripts(
            [AssetsModel::TOKEN_OUTER_ASSET => PrepareDataHelper::filterByEmptyValues($scripts)]
        );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setName(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::POPUP_NAME_KEY)) {
            return;
        }

        // trim trailing spaces for other data
        $this->_popupData->setName(
            PrepareDataHelper::filterByEmptyValues(
                $data[DataKeys::POPUP_NAME_KEY] ?? ''
            )
        );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function _setDisplaySettingId(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::DISPLAY_SETTINGS_KEY)) {
            return;
        }

        $this->_popupData->setDisplaySettingId(
            $data[DataKeys::DISPLAY_SETTINGS_KEY] ?? 0
        );
    }

    /**
     * @param array $data
     */
    protected function _setIgnoreMobile(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::IGNORE_MOBILE_KEY)) {
            return;
        }
        if (!is_null($newIgnoreMobile = $data[DataKeys::IGNORE_MOBILE_KEY] ?? null)) {
            $this->_popupData->setIgnoreMobile($newIgnoreMobile);
        }
    }

    /**
     * @param array $data
     */
    protected function _setShowType(array $data)
    {
        if (!$this->_isFieldsEditable(DataKeys::DISPLAY_TYPES_KEY)) {
            return;
        }
        if (!is_null($newShowType = $data[DataKeys::DISPLAY_TYPES_KEY] ?? null)) {
            $this->_popupData->setShowTypeId($newShowType);
        }
    }

    /**
     * @return bool
     *
     * @throws InvalidEntityTypeException
     * @throws Kohana_Exception
     * @throws ReflectionException
     * @throws Exception
     */
    protected function _validateTemplate(): bool
    {
        $validationData = $this->_getCombinedStateData()
            + $this->_getAdditionalValidationParameters();

        $templatesManager = $this->_loadTemplatesManager();

        $validator = new EditPopupValidator($templatesManager);

        if (!$validator->validate($validationData)) {
            $this->_validationErrors = ErrorFormatter::formatMessages(
                $validator->getValidationErrors()
            );

            return false;
        }

        // prepared to saving values (filtered by validation)
        $this->_templatesValues = $templatesManager->getValidData();

        if (!$this->_templatesValues) {
            $this->_validationErrors[] = 'Popup not saved!';

            return false;
        }

        return true;
    }

    /**
     * @param $fieldName
     *
     * @return bool
     */
    protected function _isFieldsEditable($fieldName): bool
    {
        return array_key_exists($fieldName, $this->_editableFields);
    }

    /**
     * @return array
     */
    protected function _combineBaseDataToSave(): array
    {
        $data = [
            DataKeys::DISPLAY_SETTINGS_KEY => $this->_popupData->getDisplaySettingId(),
            DataKeys::POPUP_NAME_KEY => $this->_popupData->getName(),
            DataKeys::HTML_ID_KEY => $this->_popupData->getHtmlID(),
            DataKeys::HTML_CLASS_KEY => $this->_popupData->getHtmlClass(),
            DataKeys::IS_ACTIVE_KEY => $this->_popupData->isActive(),
            DataKeys::IGNORE_MOBILE_KEY => $this->_popupData->isIgnoreMobile(),
            DataKeys::DISPLAY_TYPES_KEY => $this->_popupData->getShowTypeId(),
        ];

        $filteredData = $this->_filterDataByAllowedList(
            $data,
            array_intersect_key($this->_editableFields, $data)
        );

        $fields = array_replace($filteredData, array_intersect_key(static::BASE_FIELDS_KEYS_MAP, $filteredData));

        return array_combine($fields, $filteredData);
    }

    /**
     * @param array $incomingData
     *
     * @return array
     */
    protected function _filterIncomingData(array $incomingData): array
    {
        return $this->_filterDataByAllowedList($incomingData, $this->_editableFields);
    }

    /**
     * @param array $rawData
     * @param array $allowedList
     *
     * @return array
     */
    protected function _filterDataByAllowedList(array $rawData, array $allowedList): array
    {
        /** Filter values invalid _POST keys */
        $data = array_intersect_key(
            array_replace($allowedList, $rawData),
            $allowedList
        );

        /** Filter values with invalid types */
        foreach ($data as $key => $datum) {
            if (
                !isset($allowedList[$key])
                || gettype($datum) != gettype($allowedList[$key])
            ) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function _getAssetsToSave(): array
    {
        $assetsByTypes = [];

        if ($innerStyles = $this->_popupData->getCustomInnerStyles()) {
            $assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_STYLE][AssetsModel::TOKEN_INNER_ASSET] = $innerStyles;
        }

        if ($outerStyles = $this->_popupData->getCustomOuterStyles()) {
            $assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_STYLE][AssetsModel::TOKEN_OUTER_ASSET] = $outerStyles;
        }

        if ($innerScript = $this->_popupData->getCustomInnerScripts()) {
            $assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_SCRIPT][AssetsModel::TOKEN_INNER_ASSET] = $innerScript;
        }

        if ($outerScript = $this->_popupData->getCustomOuterScripts()) {
            $assetsByTypes[EntityTypesModel::ID_ENTITY_POPUP_CUSTOM_SCRIPT][AssetsModel::TOKEN_OUTER_ASSET] = $outerScript;
        }

        return $assetsByTypes;
    }

    /**
     * @return array
     */
    protected function _getAdditionalValidationParameters(): array
    {
        return [
            DataKeys::POPUP_ID_KEY => $this->_popupData->getId(),
            ConditionViewFieldsMap::VALIDATE_EVENT_TYPE_ID => $this->_eventTypeId,
        ];
    }

}