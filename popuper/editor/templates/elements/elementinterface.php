<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Editor\Templates\Elements\Formatters\FormatterInterface;
use Popuper\Editor\Validators\DynamicRulesInterface;
use Popuper\Editor\Validators\Tree\ElementInterface as ElementValidatorInterface;

/**
 * Interface ElementInterface
 *
 * @package Popuper\Editor\Templates\Elements
 *
 * @property integer id
 * @property integer parentId
 * @property integer entityId
 * @property boolean isMandatory
 * @property string  label
 * @property string  defaultTitle
 * @property integer actionType
 * @property string  description
 * @property integer fieldTypeId
 * @property string  address
 */
interface ElementInterface
{
    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType();

    /**
     * @return string
     */
    public function getDataKey();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @param ElementInterface $element
     */
    public function addChild(ElementInterface $element);

    /**
     * @return ElementInterface[]
     */
    public function getChildren();

    /**
     * @param ElementInterface $element
     *
     * @return mixed
     */
    public function setParent(ElementInterface $element);

    /**
     * @return ElementInterface
     */
    public function getParent();

    /**
     * @param ElementValidatorInterface $validator
     *
     * @return mixed
     */
    public function setValidator(ElementValidatorInterface $validator);

    /**
     * @return DynamicRulesInterface
     */
    public function getValidator();

    /**
     * @return bool
     */
    public function hasValidator();

    /**
     * @return FormatterInterface
     */
    public function getFormatter();

    /**
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter);

    /**
     * @return boolean
     */
    public function hasFormatter();

    /**
     * @return bool
     */
    public function isRoot();

}