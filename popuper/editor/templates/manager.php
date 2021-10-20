<?php

namespace Popuper\Editor\Templates;

use Kohana_Exception;
use Popuper\Editor\Templates\Elements\Factory as ElementsFactory;
use Popuper\Editor\Templates\Exceptions\InvalidEntityTypeException;
use Popuper\Editor\Templates\Tree\Builder as TreeBuilder;
use Popuper\Editor\Templates\Tree\Handlers\ElementsHandlerInterface;
use Popuper\Editor\Templates\Tree\Handlers\Getter as GetterHandler;
use Popuper\Editor\Templates\Tree\Handlers\Validator as ValidatorHandler;
use Popuper\Editor\Templates\Tree\Processor;
use Popuper\Helpers\ValuesDecorator;
use Popuper\Repositories\Buffers\DataBuffer;
use Popuper\Repositories\Data as DataRepository;
use Popuper\Repositories\Values as ValuesRepository;

/**
 * Class Manager
 *
 * @package Popuper\Editor\Templates
 */
class Manager
{
    /** @var integer */
    protected $_eventTypeId;

    /** @var integer */
    protected $_popupId;

    /** @var  array */
    protected $_templateData;

    /** @var ValuesRepository */
    protected $_valuesRepository;

    /** @var array */
    protected $_errors = [];

    /** @var array */
    protected $_validData;

    /** @var DataRepository */
    protected $_treeRepository;

    /** @var  TreeBuilder */
    protected $_treeBuilder;

    /**
     * GettingDataProcessor constructor.
     *
     * @param $eventTypeId
     * @param $popupId
     */
    public function __construct($eventTypeId, $popupId)
    {
        $this->_eventTypeId = $eventTypeId;
        $this->_popupId = $popupId;
    }

    /**
     * @param $data
     *
     * @return bool
     * @throws Kohana_Exception
     * @throws InvalidEntityTypeException
     */
    public function validate($data)
    {
        $dataBuffer = $this->_getDataBuffer()->setRawData($data);
        $itemsHandler = $this->_getValidatorHandler($dataBuffer);
        $processor = $this->_getProcessor($itemsHandler);

        if ($result = $processor->process()) {
            $this->_validData = $itemsHandler->getValidData();
        } else {
            $this->_errors = $itemsHandler->getValidationErrors();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getValuesData()
    {
        $valuesRepository = $this->_getValuesRepository();
        $valuesCollection = $valuesRepository->getCollection();

        return ValuesDecorator::denormalize($valuesCollection);
    }

    /**
     * @return array
     * @throws Kohana_Exception
     * @throws InvalidEntityTypeException
     */
    public function getTemplateData()
    {
        if ($this->_templateData === null) {
            $dataBuffer = $this->_getDataBuffer();
            $treeHandler = $this->_getGetterHandler($dataBuffer);
            $processor = $this->_getProcessor($treeHandler);

            if ($processor->process()) {
                $this->_templateData = $treeHandler->getTemplateData();
            }
        }

        return $this->_templateData;
    }

    /**
     * @return array
     */
    public function getValidData()
    {
        return $this->_validData;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->_errors;
    }

    /**
     * @return int
     */
    public function getPopupId()
    {
        return $this->_popupId;
    }

    /**
     * @return int
     */
    public function getEventTypeId()
    {
        return $this->_eventTypeId;
    }

    /**
     * @return ValuesRepository
     */
    protected function _getValuesRepository(): ValuesRepository
    {
        if ($this->_valuesRepository === null) {
            $this->_valuesRepository = new ValuesRepository($this->_popupId);
        }

        return $this->_valuesRepository;
    }

    /**
     * @return DataRepository
     */
    protected function _getDataRepository(): DataRepository
    {
        if ($this->_treeRepository === null) {
            $this->_treeRepository = new DataRepository($this->_eventTypeId);
        }

        return $this->_treeRepository;
    }

    /**
     * @return TreeBuilder
     */
    protected function _getTreeBuilder(): TreeBuilder
    {
        if($this->_treeBuilder === null) {
            $this->_treeBuilder = new TreeBuilder(
                $this->_getElementsFactory()
            );
        }

        return $this->_treeBuilder;
    }

    /**
     * @return ElementsFactory
     */
    protected function _getElementsFactory(): ElementsFactory
    {
        return new ElementsFactory();
    }

    /**
     * @param DataBuffer $dataBuffer
     *
     * @return ValidatorHandler
     */
    protected function _getValidatorHandler(DataBuffer $dataBuffer): ValidatorHandler
    {
        return new ValidatorHandler($dataBuffer);
    }

    /**
     * @param DataBuffer $dataBuffer
     *
     * @return GetterHandler
     */
    protected function _getGetterHandler(DataBuffer $dataBuffer): GetterHandler
    {
        return new GetterHandler($dataBuffer);
    }

    /**
     * @param ElementsHandlerInterface $itemsHandler
     *
     * @return Processor
     */
    protected function _getProcessor(ElementsHandlerInterface $itemsHandler): Processor
    {
        return new Processor(
            $this->_getTreeBuilder(),
            $this->_getDataRepository(),
            $itemsHandler
        );
    }

    /**
     * @return DataBuffer
     */
    protected function _getDataBuffer(): DataBuffer
    {
        return new DataBuffer();
    }

}