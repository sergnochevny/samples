<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Editor\Templates\Exceptions\InvalidEntityTypeException;
use Popuper\Repositories\ObjectItems\DataItemInterface;

/**
 * Interface ElementsFactoryInterface
 *
  * @package Popuper\Editor\Templates\Elements
 */
interface ElementsFactoryInterface
{
    /**
     * @param DataItemInterface $treeItem
     *
     * @return ElementInterface
     * @throws InvalidEntityTypeException
     */
    public function get(DataItemInterface $treeItem = null);

    /**
     * @return ElementInterface
     */
    public function getRoot();

}