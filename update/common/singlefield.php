<?php

namespace Leads\Update\Common;

use Leads\Update\ByAgent\FieldSetsBuilder;
use Leads\Update\Common\Updater as FieldsUpdater;
use Leads\Update\Exceptions\Update as UpdateException;
use Leads\Update\Field\FieldSetInterface;
use Leads\Update\Validators\Common as CommonValidator;
use Leads\Update\Validators\ErrorFormatter as UpdaterErrorFormatter;
use Leads\Update\Validators\ValidatorInterface;

/**
 * Class SingleField
 *
 * @package Leads\Update\Common
 */
class SingleField
{
    /**
     * @var integer
     */
    protected $_leadId;

    /**
     * @var string
     */
    protected $_fieldName;

    /**
     * Processing constructor.
     *
     * @param int    $leadId
     * @param string $fieldName
     */
    public function __construct(int $leadId, string $fieldName)
    {
        $this->_leadId = $leadId;
        $this->_fieldName = $fieldName;
    }

    /**
     * @param $value
     * @param array $params
     *
     * @throws \Database_TransactionException
     * @throws \Exceptions\ValidationException
     * @throws \Leads\Update\Exceptions\InvalidField
     * @throws \ReflectionException
     * @throws UpdateException
     */
    public function save($value, array $params = null)
    {
        $updater = $this->_buildUpdater();

        $updater->apply(
            $this->_prepareLeadData($value),
            $this->_getActionParameters($params)
        );
    }

    /**
     * @param array $params
     *
     * @return ActionParameters
     */
    public function _getActionParameters(array $params = null): ActionParameters
    {
        $actionParameters = $params ? [$this->_fieldName => $params] : [];

        return new ActionParameters($actionParameters);
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function _prepareLeadData($value): array
    {
        return [$this->_fieldName => $value];
    }

    /**
     * @return UpdaterErrorFormatter
     */
    protected function _getErrorFormatter(): UpdaterErrorFormatter
    {
        return new UpdaterErrorFormatter('update/common');
    }

    /**
     * @return ValidatorInterface|null
     */
    protected function _buildValidator()
    {
        $validator = $this->_getValidator();

        return $validator
            ? $validator->setErrorFormatter($this->_getErrorFormatter())
            : null;
    }

    /**
     * @return BuilderInterface
     */
    protected function _getFieldSetBuilder(): BuilderInterface
    {
        return new FieldSetsBuilder();
    }


    /**
     * @return FieldSetInterface
     * @throws \Leads\Update\Exceptions\InvalidField
     */
    protected function _buildFieldSet(): FieldSetInterface
    {
        $fieldSetBuilder = $this->_getFieldSetBuilder();

        return $fieldSetBuilder->build($this->_fieldName);
    }

    /**
     * @return FieldsUpdater
     * @throws \Leads\Update\Exceptions\InvalidField
     */
    protected function _buildUpdater(): FieldsUpdater
    {
        return (new FieldsUpdater($this->_leadId))
            ->setValidator($this->_buildValidator())
            ->addFieldSet($this->_buildFieldSet());
    }

    /**
     * @return CommonValidator|null
     */
    protected function _getValidator()
    {
        return new CommonValidator();
    }
}