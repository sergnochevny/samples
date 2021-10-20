<?php

namespace Popuper\Editor\Templates\Tree\Handlers;

use Popuper\Editor\Templates\Elements\ElementInterface;
use ReflectionException;

/**
 * Class Validator
 *
  * @package Popuper\Editor\Templates\Tree\Handlers
 */
class Validator extends ElementsHandlerAbstract
{
    /**
     * @param ElementInterface $element
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function handleItem(ElementInterface $element)
    {
        if (!$element->hasValidator()) {
            return true;
        }

        $rawData = $this->_dataBuffer->getRawData();

        $elementValidator = $element->getValidator();
        $elementValidator->load($rawData);

        if ($result = $elementValidator->validate()) {
            $this->_collectValidData($element, $rawData);
        } else {
            $this->_collectValidationErrors($element);
        }

        return $result;
    }

    /**
     * @param ElementInterface $element
     *
     * @return mixed
     * @see modules/popuper/classes/popuper/templates/tree/processor.php:96
     *
     * if there is no item data
     * we don't need to check the child elements
     */
    public function allowHandleChildren(ElementInterface $element)
    {
        if (!$element) {
            return true;
        }

        return $this->_dataBuffer->hasDataById($element->id);
    }

    /**
     * @return array
     */
    public function getValidData()
    {
        $validData = $this->_dataBuffer->getValidData();

        if (!empty($validData)) {
            $validData = array_merge(...$validData);
        }

        return $validData;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->_dataBuffer->getValidationErrors();
    }

    /**
     * @param ElementInterface $element
     * @param array            $rawData
     */
    protected function _collectValidData(ElementInterface $element, array $rawData = [])
    {
        if ($element->hasFormatter()) {
            $elementFormatter = $element->getFormatter();
            $elementData = $elementFormatter->getSavingData($rawData);
            $this->_dataBuffer->addDataById($element->id, $elementData);
        }
    }

    /**
     * @param ElementInterface $element
     */
    protected function _collectValidationErrors(ElementInterface $element)
    {
        $elementValidator = $element->getValidator();
        $this->_dataBuffer->addValidationErrors(
            $elementValidator->getValidationErrors()
        );
    }

}