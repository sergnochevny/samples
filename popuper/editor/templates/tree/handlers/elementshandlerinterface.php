<?php

namespace Popuper\Editor\Templates\Tree\Handlers;

use Popuper\Editor\Templates\Elements\ElementInterface;

/**
 * Interface ElementsHandlerInterface
 *
  * @package Popuper\Editor\Templates\Tree\Handlers
 */
interface ElementsHandlerInterface
{
    /**
     * @param ElementInterface $element
     *
     * @return mixed
     */
    public function handleItem(ElementInterface $element);

    /**
     * @param ElementInterface $element
     *
     * @return mixed
     */
    public function allowHandleChildren(ElementInterface $element);

    /**
     * @param ElementInterface[] $elements
     * @param ElementInterface   $parent
     *
     * @return bool
     */
    public function collectProcessedChildrenData(array $elements, ElementInterface $parent);

}