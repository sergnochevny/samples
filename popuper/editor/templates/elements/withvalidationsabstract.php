<?php

namespace Popuper\Editor\Templates\Elements;

use Exception;
use Popuper\Helpers\Validation;

/**
 * Class WithValidations
 *
 * @package Popuper\Editor\Templates\Elements
 *
 * @property string validations
 */
abstract class WithValidationsAbstract extends ElementAbstract
{
    /** @var array */
    protected $_validations;

    /**
     * @return array|null
     * @throws Exception
     */
    public function getValidations()
    {
        if ($this->hasValidator()) {
            $this->_validator->applyAdditionalRules();

            return Validation::formatRules(
                $this->_validator->getRules(),
                $this->_validator->getFieldName()
            );
        }

        return null;
    }

}