<?php

namespace Popuper\Common;

use ArrayIterator;
use IteratorAggregate;
use stdClass;
use Traversable;

/**
 * Class DataObject
 *
  * @package Popuper\Common
 */
abstract class DataObject implements IteratorAggregate
{
    /**
     * DataObject constructor.
     *
     * @param $attributes
     */
    public function __construct($attributes = null)
    {
        $this->load($attributes);
    }

    /**
     * @param array|stdClass $properties
     */
    public function load($properties)
    {
        if (!empty($properties)) {
            foreach ($properties as $name => $value) {
                $property = '_' . lcfirst($name);
                $method = 'set' . ucfirst($name);
                if (method_exists($this, $method)) {
                    call_user_func_array([$this, $method], [$value]);
                } elseif (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        }
    }

    /**
     * Retrieve an external iterator
     *
     * @link  https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator(array_values($this->toArray()));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];

        $properties = $this->_getObjectVars();
        foreach ($properties as $property => $value) {
            if ($value instanceof DataObject) {
                $value = $value->toArray();
            } elseif (is_array($value)) {
                foreach ($value as &$_value) {
                    if ($_value instanceof DataObject) {
                        $_value = $_value->toArray();
                    }
                }
            }

            $this->_addKeyValue($array, $property, $value);
        }

        return $array;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        } else {
            $property = '_' . $name;
            if (property_exists($this, $property)) {
                return $this->{$property};
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->load([$name => $value]);
    }

    /**
     * @param array  $array
     * @param string $key (class property name)
     * @param mixed  $value
     */
    protected function _addKeyValue(array &$array, $key, $value)
    {
        $array[ltrim($key, '_')] = $value;
    }

    /**
     * @return array
     */
    protected function _getObjectVars()
    {
        $properties = get_object_vars($this);

        foreach ($properties as $property => &$value) {
            $method = 'get' . ucfirst(ltrim($property, '_'));
            if (method_exists($this, $method)) {
                $value = call_user_func([$this, $method]);
            }
        }

        return $properties;
    }

}