<?php

namespace Popuper\Editor\Templates\Tree;

use ConditionsTree\Interfaces\IRowsProvider;
use Kohana_Exception;
use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Editor\Templates\Elements\ElementsFactoryInterface;
use Popuper\Editor\Templates\Exceptions\InvalidEntityTypeException;
use Popuper\Editor\Templates\Tree\BuilderInterface as TreeBuilderInterface;

/**
 * Class Builder
 *
  * @package Popuper\Editor\Templates\Tree
 */
class Builder implements TreeBuilderInterface
{
    /** @var IRowsProvider */
    protected $_collection;

    /** @var ElementInterface */
    protected $_treeRoot;

    /** @var ElementsFactoryInterface */
    protected $_elementsFactory;

    /**
     * Builder constructor.
     *
     * @param ElementsFactoryInterface $treeItemsBuilder
     */
    public function __construct(ElementsFactoryInterface $treeItemsBuilder)
    {
        $this->_elementsFactory = $treeItemsBuilder;
    }

    /**
     * @param IRowsProvider $collection
     *
     * @return Builder
     */
    public function setCollection(IRowsProvider $collection)
    {
        $this->_collection = $collection;

        return $this;
    }

    /**
     * @return ElementInterface|null
     * @throws Kohana_Exception
     * @throws InvalidEntityTypeException
     */
    public function build()
    {
        if ($this->_treeRoot === null) {
            if ($this->_elementsFactory !== null) {
                $this->_treeRoot = $this->_elementsFactory->getRoot();
                $this->_buildTree($this->_treeRoot);
            }
        }

        return $this->_treeRoot;
    }

    /**
     * It builds a tree from a collection of handlers
     *
     * @param ElementInterface $parent
     *
     * @throws Kohana_Exception
     * @throws InvalidEntityTypeException
     */
    protected function _buildTree(ElementInterface $parent)
    {
        if (
            ($this->_collection !== null)
            && $this->_collection->count()
        ) {
            /**
             * this collection's rewind is need to processing
             * non sequential ids of elements in the collection
             * (ex. when parent element is a last row of collection)
             */
            $this->_collection->rewind();

            while ($this->_collection->valid()) {
                $dataItem = $this->_collection->current();
                $element = $this->_elementsFactory->get($dataItem);

                $itemKey = $this->_collection->key();
                $this->_collection->next();

                if (
                    ($element instanceof ElementInterface)
                    && $this->_isRelate($element, $parent)
                ) {
                    $this->_collection->remove($itemKey);

                    $parent->addChild($element);
                    $this->_buildTree($element);
                }
            }

            /**
             * this collection's rewind is need for further processing
             * of elements when returning to a higher level
             */
            $this->_collection->rewind();
        }
    }

    /**
     * @param ElementInterface $element
     * @param ElementInterface $parent
     *
     * @return bool
     */
    protected function _isRelate(ElementInterface $element, ElementInterface $parent)
    {
        return (
                ($parent !== null)
                && ($element->parentId == $parent->id)
            )
            || (
                ($parent === null)
                && ($element->parentId === null)
            );
    }

}