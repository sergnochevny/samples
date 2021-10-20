<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Common\DataObject;
use Popuper\Editor\Templates\Elements\Formatters\FormatterInterface;
use Popuper\Editor\Validators\DynamicRulesInterface;
use Popuper\Editor\Validators\Tree\ElementInterface as ElementValidatorInterface;

/**
 * Class Element
 *
 * @package Popuper\Editor\Templates\Elements
 */
abstract class ElementAbstract extends DataObject implements ElementInterface
{
    /** @var ElementValidatorInterface */
    protected $_validator;

    /** @var integer */
    protected $_id;

    /** @var integer */
    protected $_parentId;

    /** @var integer */
    protected $_entityId;

    /** @var bool */
    protected $_isMandatory;

    /** @var string */
    protected $_label;

    /** @var string */
    protected $_description;

    /** @var string */
    protected $_address;

    /** @var integer */
    protected $_fieldTypeId;

    /** @var integer */
    protected $_actionType;

    /** @var string */
    protected $_defaultTitle;

    /** @var FormatterInterface */
    protected $_formatter;

    /** @var ElementInterface[] */
    protected $_children = [];

    /** @var ElementInterface */
    protected $_parent;

    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [];

    /**
     * @return string
     */
    public function getDataKey()
    {
        return lcfirst($this->getType());
    }

    /**
     * @param ElementInterface $element
     */
    public function addChild(ElementInterface $element)
    {
        $this->_children[$element->id] = $element;

        $element->setParent($this);
    }

    /**
     * @return ElementInterface[]
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * @return DynamicRulesInterface
     */
    public function getValidator()
    {
        return $this->_validator;
    }

    /**
     * @param ElementValidatorInterface $validator
     *
     * @return mixed
     */
    public function setValidator(ElementValidatorInterface $validator)
    {
        $this->_validator = $validator;
    }

    /**
     * @return bool
     */
    public function hasValidator()
    {
        return ($this->_validator instanceof DynamicRulesInterface);
    }

    /**
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        return $this->_formatter;
    }

    /**
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->_formatter = $formatter;
    }

    /**
     * @return bool
     */
    public function hasFormatter()
    {
        return ($this->_formatter instanceof FormatterInterface);
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        if ($id !== null) {
            $this->_id = (int) $id;
        }
    }

    /**
     * @param $parentId
     */
    public function setParentId($parentId)
    {
        if ($parentId !== null) {
            $this->_parentId = (int) $parentId;
        }
    }

    /**
     * @param $entity
     */
    public function setEntityId($entity)
    {
        if ($entity !== null) {
            $this->_entityId = (int) $entity;
        }
    }

    /**
     * @param $isMandatory
     */
    public function setIsMandatory($isMandatory)
    {
        if ($isMandatory !== null) {
            $this->_isMandatory = (bool) $isMandatory;
        }
    }

    /**
     * @param $fieldTypeId
     */
    public function setFieldTypeId($fieldTypeId)
    {
        if ($fieldTypeId !== null) {
            $this->_fieldTypeId = (int)$fieldTypeId;
        }
    }

    /**
     * @param $actionType
     */
    public function setActionType($actionType)
    {
        if ($actionType !== null) {
            $this->_actionType = (int)$actionType;
        }
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return ($this->id === null);
    }

    /**
     * @return ElementInterface
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * @param ElementInterface $parent
     */
    public function setParent(ElementInterface $parent)
    {
        $this->_parent = $parent;
    }

    /**
     * @return array
     */
    protected function _getObjectVars()
    {
        $list = parent::_getObjectVars();

        $allowedList = $this->_allowedTemplateAttributes;

        // protected properties with prefix
        array_walk(
            $allowedList,
            function (&$value) {
                $value = '_' . $value;
            }
        );

        return array_intersect_key($list, array_flip($allowedList));
    }

}