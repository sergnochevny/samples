<?php

namespace Popuper\Editor\Templates\Tree\Handlers;

use Popuper\Editor\Templates\Elements\ElementInterface;
use Popuper\Repositories\Buffers\BufferInterface;

/**
 * Class ElementsHandlerAbstract
 *
  * @package Popuper\Editor\Templates\Tree\Handlers
 */
abstract class ElementsHandlerAbstract implements ElementsHandlerInterface
{

    /** @var BufferInterface */
    protected $_dataBuffer;

    /**
     * Item constructor.
     *
     * @param BufferInterface $buffer
     */
    public function __construct(BufferInterface $buffer)
    {
        $this->_dataBuffer = $buffer;
    }

    /**
     * @param ElementInterface[] $elements
     * @param ElementInterface   $parent
     *
     * @return bool
     */
    public function collectProcessedChildrenData(array $elements, ElementInterface $parent)
    {
        // we don't need to do anything here
        return true;
    }

}