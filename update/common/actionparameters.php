<?php

namespace Leads\Update\Common;

/**
 * Class ActionParameters
 *
 * @package Leads\Update\Common
 */
class ActionParameters
{
    /**
     * @var array
     */
    protected $_parameters = [];

    /**
     * Parameters constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if ($parameters) {
            $this->load($parameters);
        }
    }

    /**
     * @param array $parameters
     */
    public function load(array $parameters)
    {
        $this->_parameters = $parameters;
    }

    /**
     * @param string $parameter
     *
     * @return mixed|null
     */
    public function get(string $parameter)
    {
        return $this->_parameters[$parameter] ?? null;
    }
}