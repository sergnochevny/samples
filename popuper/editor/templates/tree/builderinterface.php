<?php

namespace Popuper\Editor\Templates\Tree;

use ConditionsTree\Interfaces\IRowsProvider;
use Popuper\Editor\Templates\Elements\ElementInterface;

/**
 * Interface BuilderInterface
 *
  * @package Popuper\Editor\Templates\Tree
 */
interface BuilderInterface
{
    /**
     * @return ElementInterface
     */
    public function build();

    /**
     * @param IRowsProvider $collection
     *
     * @return Builder
     */
    public function setCollection(IRowsProvider $collection);

}