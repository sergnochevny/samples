<?php

namespace Popuper\Common;

use ConditionsTree\Providers\RowsProvider;
use Database_MySQLi_Result;

/**
 * Class Collection
 *
  * @package Popuper\Common
 */
class Collection extends RowsProvider
{
    /** @var bool */
    protected $_loaded = false;

    /**
     * Collection constructor.
     *
     * @param null|array|Database_MySQLi_Result $data
     */
    public function __construct($data = null)
    {
        $this->load($data);
    }

    /**
          *
     * @param null|array|Database_MySQLi_Result
     *
     */
    public function load($data = null)
    {
        if (!$this->_loaded && $data !== null) {
            $this->_populate($data);
            $this->_loaded = true;
        }
    }

    /**
          *
     * @param Database_MySQLi_Result|array $data
     *
     */
    protected function _populate($data)
    {
        $this->_resetRows();

        if ($data !== null) {
            foreach ($data as $row) {
                $this->_addRow($row);
            }
        }
    }

}