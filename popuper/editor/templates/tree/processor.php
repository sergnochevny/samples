<?php

namespace Popuper\Editor\Templates\Tree;

use Kohana_Exception;
use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Editor\Templates\Exceptions\InvalidEntityTypeException;
use Popuper\Editor\Templates\Tree\BuilderInterface as TreeBuilderInterface;
use Popuper\Editor\Templates\Tree\Handlers\ElementsHandlerInterface;
use Popuper\Repositories\RepositoryInterface;

/**
 * Class Processor
 *
 * @package Popuper\Editor\Templates\Tree
 */
final class Processor implements ProcessorInterface
{
    /** @var bool */
    protected $_isProcessed = false;

    /** @var bool */
    protected $_mainResult;

    /** @var RepositoryInterface */
    protected $_repository;

    /** @var  Builder */
    protected $_builder;

    /** @var ElementsHandlerInterface */
    protected $_handler;

    /**
     * Processor constructor.
     *
     * @param BuilderInterface         $treeBuilder
     * @param RepositoryInterface      $treeRepository
     * @param ElementsHandlerInterface $handler
     */
    public function __construct(
        TreeBuilderInterface $treeBuilder,
        RepositoryInterface $treeRepository,
        ElementsHandlerInterface $handler
    ) {
        $this->_repository = $treeRepository;
        $this->_handler = $handler;
        $this->_builder = $treeBuilder;
    }

    /**
     * @return bool
     * @throws Kohana_Exception
     * @throws InvalidEntityTypeException
     */
    public function process()
    {
        if (!$this->_isProcessed) {
            $tree = $this->_buildTree();
            $this->_mainResult = $this->_processTree($tree);
            $this->_isProcessed = true;
        }

        return $this->_mainResult;
    }

    /**
     * @return ElementInterface
     * @throws Kohana_Exception
     * @throws InvalidEntityTypeException
     */
    protected function _buildTree()
    {
        $this->_builder->setCollection(
            $this->_repository->getCollection()
        );

        return $this->_builder->build();
    }

    /**
     * @param ElementInterface $element
     *
     * @return bool|mixed
     */
    protected function _processTree(ElementInterface $element)
    {
        $handler = $this->_handler;

        //element handling
        $result = $handler->handleItem($element);

        // Root always has no data
        // but we always need to process its children
        if (
            $result
            && !$handler->allowHandleChildren($element)
            && !$element->isRoot()
        ) {
            return true;
        }

        if ($result) {
            $processedItems = [];

            //children handling
            $children = $element->getChildren();
            while ($child = array_shift($children)) {
                // we need to collect all errors
                if ($result = $this->_processTree($child) && $result) {
                    $processedItems[] = $child;
                }
            }

            // collect all children data for processed items
            $handler->collectProcessedChildrenData($processedItems, $element);
        }

        return $result;
    }

}