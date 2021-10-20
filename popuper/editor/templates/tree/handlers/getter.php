<?php

namespace Popuper\Editor\Templates\Tree\Handlers;

use Popuper\Editor\Templates\Elements\ElementInterface;

/**
 * Class Getter
 *
 * @package Popuper\Editor\Templates\Tree\Handlers
 */
class Getter extends ElementsHandlerAbstract
{
    const ROOT_ENTITY = 'root';

    /**
     * @param ElementInterface $element
     *
     * @return mixed
     *
     * The Root item is an abstract grouped entity
     * and doesn't have an element
     * but we need to process its child items
     * @see modules/popuper/classes/popuper/templates/tree/processor.php:96
     */
    public function handleItem(ElementInterface $element)
    {
        if (
            ($element instanceof ElementInterface)
            && $element->hasFormatter()
        ) {
            $elementFormatter = $element->getFormatter();
            $elementData = $elementFormatter->getTemplateData();
            $this->_dataBuffer->addDataById($element->id, $elementData);
        }

        return true;
    }

    /**
     * @param ElementInterface[] $elements
     * @param ElementInterface   $parent
     *
     * @return bool
     */
    public function collectProcessedChildrenData(array $elements, ElementInterface $parent)
    {
        foreach ($elements as $element) {
            if ($element->hasFormatter()) {
                $elementFormatter = $element->getFormatter();

                $collectedData = $this->_dataBuffer->extractDataById($element->id);

                $elementData = $elementFormatter->formatCollectedData($collectedData);

                $dataId = $parent->isRoot() ? static::ROOT_ENTITY : $parent->id;

                $this->_dataBuffer->addDataById($dataId, $elementData);
            }
        }

        return parent::collectProcessedChildrenData($elements, $parent);
    }

    /**
     * @param ElementInterface $element
     *
     * @return mixed
     * @see modules/popuper/classes/popuper/templates/tree/processor.php:96
     *
     * we always need to process the child items
     * to get the tree data
     */
    public function allowHandleChildren(ElementInterface $element)
    {
        return true;
    }

    /**
     * @return array
     */
    public function getTemplateData()
    {
        return $this->_dataBuffer->getDataById(static::ROOT_ENTITY, []);
    }

}