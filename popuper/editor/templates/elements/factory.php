<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Editor\Templates\Elements\FieldTypes\Factory as FieldTypesFactory;
use Popuper\Editor\Templates\Elements\FieldTypes\FieldTypeInterface;
use Popuper\Editor\Templates\Elements\Formatters\Element as ElementFormatter;
use Popuper\Editor\Templates\Elements\Formatters\Field as FieldFormatter;
use Popuper\Editor\Templates\Elements\Formatters\File as FileFormatter;
use Popuper\Editor\Templates\Elements\Formatters\ShortCode as ShortCodeFormatter;
use Popuper\Editor\Templates\Exceptions\InvalidEntityTypeException;
use Popuper\Editor\Validators\Tree\ElementInterface as ElementValidatorInterface;
use Popuper\Editor\Validators\Tree\Fields\Factory as FieldValidatorsFactory;
use Popuper\Editor\Validators\Tree\Grouped as GroupedValidator;
use Popuper\Editor\Validators\Tree\GroupedRequired as GroupedRequiredValidator;
use Popuper\Editor\Validators\Tree\MultiSelected as MultiSelectedValidator;
use Popuper\Editor\Validators\Tree\OneSelected as OneSelectedValidator;
use Popuper\Editor\Validators\Tree\Root as RootValidator;
use Popuper\Editor\Validators\Tree\ShortCodes as ShortCodesValidator;
use Popuper\Models\EntityTypes as EntityTypesModel;
use Popuper\Repositories\ObjectItems\DataItemInterface;

/**
 * Class Factory
 *
 * @package Popuper\Editor\Templates\Tree\Items
 */
class Factory implements ElementsFactoryInterface
{
    /**
     * @param DataItemInterface $treeItem
     *
     * @return ElementInterface
     * @throws InvalidEntityTypeException
     * @throws \Exception
     */
    public function get(DataItemInterface $treeItem = null)
    {
        switch ($treeItem->entityTypeId) {
            case EntityTypesModel::ID_ENTITY_POPUP_TYPE:
                $element = new PopupType((array) $treeItem);
                $this->_fillOneSelectedElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_PATTERN:
                $element = new Pattern((array) $treeItem);
                $this->_fillOneSelectedElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_BUTTON:
                $element = new Button((array) $treeItem);
                $this->_fillGroupedElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_BUTTON_STYLE:
                $element = new ButtonStyle((array) $treeItem);
                $this->_fillOneSelectedElement($element);

                break;
            case EntityTypesModel::ID_ENTITY_ACTION:
                $element = new Action((array) $treeItem);
                $this->_fillMultiSelectedElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_SHORT_CODE:
                $element = new ShortCode((array) $treeItem);
                $this->_fillShortCodeElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_TEMPLATE_SCRIPT:
                $element = new Script((array) $treeItem);
                $this->_fillFileElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_TEMPLATE_STYLE:
                $element = new Style((array) $treeItem);
                $this->_fillFileElement($element);

                break;

            case EntityTypesModel::ID_ENTITY_FIELD:
                $element = new Field((array) $treeItem);
                $this->_fillFieldElement($element);

                break;

            default:
                throw new InvalidEntityTypeException('Invalid Entity Type.');
        }

        return $element;
    }

    /**
     * @return ElementInterface
     */
    public function getRoot()
    {
        $element = new Root([]);

        $element->setValidator(
            new RootValidator($element)
        );

        return $element;
    }

    /**
     * @param ElementInterface $element
     */
    protected function _fillFileElement(ElementInterface $element)
    {
        $element->setFormatter(
            new FileFormatter($element)
        );
    }

    /**
     * @param ElementInterface $element
     */
    protected function _fillGroupedElement(ElementInterface $element)
    {
        $element->setFormatter(
            new ElementFormatter($element)
        );

        if ($element->isMandatory) {
            $validator = new GroupedRequiredValidator($element);
        } else {
            $validator = new GroupedValidator($element);
        }

        $element->setValidator($validator);
    }

    /**
     * @param ElementInterface $element
     */
    protected function _fillShortCodeElement(ElementInterface $element)
    {
        $element->setFormatter(
            new ShortCodeFormatter($element)
        );

        $element->setValidator(
            new ShortCodesValidator($element)
        );
    }

    /**
     * @param ElementInterface $element
     */
    protected function _fillOneSelectedElement(ElementInterface $element)
    {
        $element->setFormatter(
            new ElementFormatter($element)
        );

        $element->setValidator(
            new OneSelectedValidator($element)
        );
    }

    /**
     * @param ElementInterface $element
     */
    protected function _fillMultiSelectedElement(ElementInterface $element)
    {
        $element->setFormatter(
            new ElementFormatter($element)
        );

        $element->setValidator(
            new MultiSelectedValidator($element)
        );
    }

    /**
     * @param Field $element
     *
     * @throws \Exception
     */
    protected function _fillFieldElement(Field $element)
    {
        $element->setFormatter(
            new FieldFormatter($element)
        );

        $element->setFieldType(
            $this->_getFieldType($element)
        );

        $element->setValidator(
            $this->_getFieldValidator($element)
        );
    }

    /**
     * @param ElementInterface $element
     *
     * @return ElementValidatorInterface
     * @throws \Exception
     */
    protected function _getFieldValidator(ElementInterface $element): ElementValidatorInterface
    {
        return FieldValidatorsFactory::get($element);
    }

    /**
     * @param ElementInterface $element
     *
     * @return FieldTypeInterface
     * @throws \Popuper\Editor\Templates\Exceptions\InvalidFieldTypeException
     */
    protected function _getFieldType(ElementInterface $element): FieldTypeInterface
    {
        return FieldTypesFactory::get($element);
    }

}