<?php

namespace Popuper\Editor\Templates\Elements;

use Popuper\Editor\Templates\Elements\FieldTypes\FieldTypeInterface;
use Popuper\Helpers\DataKeys;
use Popuper\Models\EntityTypes as EntityTypesModel;

/**
 * Class Field
 *
 * @package Popuper\Editor\Templates\Elements
 */
class Field extends WithValidationsAbstract
{
    /** @var integer */
    protected $_inputType;

    /** @var FieldTypeInterface */
    protected $_fieldType;

    /**
     * @var array
     */
    protected $_allowedTemplateAttributes = [
        DataKeys::ID_KEY,
        DataKeys::LABEL_KEY,
        DataKeys::INPUT_TYPE_KEY,
        DataKeys::VALIDATIONS_KEY,
    ];

    /**
     * @return string (this returns a type from model popupsEntityTypes)
     */
    public function getType()
    {
        return EntityTypesModel::ENTITY_FIELD;
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        return DataKeys::FIELDS_KEY;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        if($this->_inputType === null) {
            $this->_inputType = DataKeys::INPUT_TYPE_TEXT;

            if ($this->_fieldType instanceof FieldTypeInterface) {
                $this->_inputType = $this->_fieldType->getInputType();
            }
        }

        return $this->_inputType;
    }

    /**
     * @param FieldTypeInterface $fieldType
     */
    public function setFieldType(FieldTypeInterface $fieldType)
    {
        $this->_fieldType = $fieldType;
    }

    /**
     * @return null|FieldTypeInterface
     */
    public function getFieldType()
    {
        return $this->_fieldType;
    }

}