<?php

namespace Leads\Update\Common;

use DB;
use Exception;
use Exceptions\ValidationException;
use Leads\Update\Actions\ActionInterface;
use Leads\Update\Actions\Throwable\ThrowableInterface;
use Leads\Update\Field\Data as FieldData;
use Leads\Update\Field\DataInterface;
use Leads\Update\Field\FieldSetInterface;
use Leads\Update\Validators\ValidatorInterface;

/**
 * Class Updater
 *
 * @package Leads\Update\Common
 */
class Updater
{
    /**
     * @var FieldSetInterface[]
     */
    protected $_fieldSets;

    /**
     * @var ValidatorInterface|null
     */
    protected $_validator;

    /**
     * @var int
     */
    protected $_leadId;

    /**
     * Updater constructor.
     *
     * @param $leadId
     */
    public function __construct($leadId)
    {
        $this->_leadId = $leadId;
    }


    /**
     * @throws ValidationException
     * @throws \Database_TransactionException
     * @throws \ReflectionException
     */
    public function apply(array $data, ActionParameters $params)
    {
        $this->_validate($data);
        $this->_save($data, $params);
    }

    /**
     * @param ValidatorInterface $validator
     *
     * @return Updater
     */
    public function setValidator(ValidatorInterface $validator = null): Updater
    {
        $this->_validator = $validator;

        return $this;
    }

    /**
     * @param FieldSetInterface $fieldSet
     *
     * @return Updater
     */
    public function addFieldSet(FieldSetInterface $fieldSet): Updater
    {
        $this->_fieldSets[$fieldSet->getName()] = $this->_fieldSets[$fieldSet->getName()] ?? $fieldSet;

        return $this;
    }

    /**
     * @throws ValidationException
     * @throws \ReflectionException
     */
    protected function _validate(array $data)
    {
        if ($this->_validator) {
            $this->_validator
                ->setLeadId($this->_leadId)
                ->setFieldSets($this->_fieldSets)
                ->load($data);

            if (!$this->_validator->validate()) {
                throw (new ValidationException())->setErrors(
                    $this->_validator->getValidationErrors()
                );
            }
        }
    }

    /**
     * @param array            $data
     * @param ActionParameters $params
     *
     * @throws \Database_TransactionException
     */
    protected function _save(array $data, ActionParameters $params)
    {
        DB::startTransaction();

        try {
            $actionsQueue = $this->_collectActionsWithData($this->_fieldSets, $data);

            /**
             * @var ActionInterface     $action
             * @var FieldSetInterface[] $fieldSets
             */
            foreach ($actionsQueue as $action => $fieldSets) {
                try {
                    $action->execute($fieldSets, $params);
                } catch (Exception $exception) {
                    if ($action instanceof ThrowableInterface) {
                        throw $exception;
                    }
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();

            throw $exception;
        }
    }

    /**
     * @param FieldSetInterface[] $fieldSets
     *
     * @return ActionsQueue
     */
    protected function _collectActionsWithData(array $fieldSets, array $data): ActionsQueue
    {
        $actionsQueue = $this->_getActionsQueue();

        /**
         * here while loop is used to be able to expand $fieldSets during the pass
         */
        $fieldSet = reset($fieldSets);
        while ($fieldSet) {
            $fieldData = $this->_buildFieldData($fieldSet, $data);

            if ($this->_isNeedToUpdateField($fieldSet, $fieldData)) {
                $fieldSet->setData($fieldData);

                if ($actions = $fieldSet->getActions()) {
                    foreach ($actions as $actionClass) {
                        $action = $this->_getActionInstance($actionClass);
                        $actionsQueue->insert($action, $fieldSet);
                    }
                }

                if ($relatedFieldSets = $fieldSet->getRelatedFieldSets()) {
                    foreach ($relatedFieldSets as $relatedFieldSet) {
                        $name = $relatedFieldSet->getName();

                        $fieldSets[$name] = $fieldSets[$name] ?? $relatedFieldSet;
                    }
                }
            }

            $fieldSet = next($fieldSets);
        }

        return $actionsQueue;
    }

    /**
     * @param FieldSetInterface $fieldSet
     * @param DataInterface     $fieldData
     *
     * @return bool
     */
    protected function _isNeedToUpdateField(FieldSetInterface $fieldSet, DataInterface $fieldData): bool
    {
        if ($fieldData->getNewValue() == $fieldData->getOldValue()) {
            return false;
        }

        if ($checker = $fieldSet->getConditionUpdateChecker()) {
            return $checker->isNeedToUpdate($this->_leadId, $fieldSet);
        }

        return true;
    }

    /**
     * @param FieldSetInterface $fieldSet
     * @param array             $data
     *
     * @return FieldData
     */
    protected function _buildFieldData(FieldSetInterface $fieldSet, array $data)
    {
        $newValue = $data[$fieldSet->getName()] ?? null;

        if ($valueFormatter = $fieldSet->getDataPreparer()) {
            $newValue = $valueFormatter->getValue($this->_leadId, $data, $fieldSet);
        }

        $currentValue = $fieldSet->getFieldStore()->getCurrentValue($this->_leadId);

        return $this->_getFieldData($currentValue, $newValue);
    }

    /**
     * @param string $actionClass
     *
     * @return ActionInterface
     */
    protected function _getActionInstance(string $actionClass)
    {
        /**
         * @var $actionClass ActionInterface
         */
        return $actionClass::getInstance($this->_leadId);
    }

    /**
     * @param $currentValue
     * @param $valueToSave
     *
     * @return FieldData
     */
    protected function _getFieldData($currentValue, $valueToSave): DataInterface
    {
        return new FieldData($currentValue, $valueToSave);
    }

    /**
     * @return ActionsQueue
     */
    protected function _getActionsQueue(): ActionsQueue
    {
        return new ActionsQueue();
    }

}