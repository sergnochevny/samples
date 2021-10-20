<?php

namespace Popuper\Editor;

use Database_TransactionException;
use DB;
use Exception;
use Kohana_Exception;
use Popuper\Editor\Popup as PopupEditor;
use Popuper\Helpers\Cache\Popups as PopupsCache;
use Popuper\Helpers\DataKeys;
use Popuper\Models\Events\Types as EventTypesModel;
use Popuper\Models\PopuperRevisions as PopuperRevisionsModel;

/**
 * Class Event
 *
  * @package Popuper\Editor
 */
class Event
{
    /** @var int */
    protected $_eventTypeId = 0;

    /** @var array */
    protected $_validationErrors = [];

    /** @var int */
    protected $_defaultEventId = EventTypesModel::EVENT_UNSPECIFIED_ONE_TIME;

    /**
     * Function __construct
     * Single constructor.
     *
     * @param $eventTypeId
     */
    public function __construct($eventTypeId)
    {
        $this->_eventTypeId = $eventTypeId;

        if (!$this->_eventTypeId) {
            $this->_eventTypeId = $this->_defaultEventId;
        }
    }

    /**
     * @param array $popupsData
     *
     * @return bool
     * @throws Database_TransactionException
     * @throws Exception
     */
    public function saveRelatedPopupsList(array $popupsData)
    {
        $result = false;

        DB::startTransaction();
        try {
            $popupsOrder = 1;
            $revisionId = $this->_getRevision();

            foreach ($popupsData as $popupValues) {
                $popupId = $popupValues[DataKeys::ID_KEY] ?? null;

                if (!$popupId) {
                    continue;
                }

                $partialValues = $this->_buildPopupPartialData($popupValues, $popupsOrder++);

                /** @var PopupEditor $popupEditor */
                $popupEditor = PopupEditor::getListPositionEditor($this->_eventTypeId, $popupId)
                    ->setRevisionId($revisionId)
                    ->setData($partialValues);

                $popupSaved = $popupEditor->save();

                $result = $result || $popupSaved;
            }

            if ($result) {
                PopupsCache::removeAllByPrefix();
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
     * @return mixed
     * @throws Kohana_Exception
     */
    protected function _getRevision()
    {
        return PopuperRevisionsModel::getInstance()->initNew();
    }

    /**
     * @param     $popupValues
     * @param int $popupsOrder
     *
     * @return array
     */
    protected function _buildPopupPartialData($popupValues, int $popupsOrder): array
    {
        return [
            DataKeys::IS_ACTIVE_KEY => (bool) ($popupValues[DataKeys::IS_ACTIVE_KEY] ?? null),
            DataKeys::ORDER_FOR_EVENT_KEY => $popupsOrder,
        ];
    }

}