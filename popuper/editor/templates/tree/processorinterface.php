<?php

namespace Popuper\Editor\Templates\Tree;

use ReflectionException;

/**
 * Interface ProcessorInterface
 *
  * @package Popuper\Editor\Templates\Tree
 */
interface ProcessorInterface
{
    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function process();

}