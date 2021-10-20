<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Editor\Templates\Tree\Handlers\Getter;

/**
 * Abstract Root element for the either tree
 *
 * Class Root
 *
  * @package Popuper\Editor\Templates\Elements
 */
class Root extends ElementAbstract
{
    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return Getter::ROOT_ENTITY;
    }

}