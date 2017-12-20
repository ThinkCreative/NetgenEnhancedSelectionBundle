<?php

namespace Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

class Type extends FieldType
{
    /**
     * List of settings available for this FieldType.
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $settingsSchema = array(
        'options' => array(
            'type' => 'array',
            'default' => array(),
        ),
        'isMultiple' => array(
            'type' => 'boolean',
            'default' => false,
        ),
        'delimiter' => array(
            'type' => 'string',
            'default' => '',
        ),
        'query' => array(
            'type' => 'string',
            'default' => '',
        ),
    );

    /**
     * Returns the field type identifier for this field type.
     *
     * This identifier should be globally unique and the implementer of a
     * FieldType must take care for the uniqueness. It is therefore recommended
     * to prefix the field-type identifier by a unique string that identifies
     * the implementer. A good identifier could for example take your companies main
     * domain name as a prefix in reverse order.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'sckenhancedselection';
    }

    /**
     * Returns a human readable string representation from the given $value.
     *
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     *
     * The used $value can be assumed to be already accepted by {@link * acceptValue()}.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string) $value;
    }

    /**
     * Returns the empty value for this field type.
     *
     * This value will be used, if no value was provided for a field of this
     * type and no default value was specified in the field definition. It is
     * also used to determine that a user intentionally (or unintentionally) did not
     * set a non-empty value.
     *
     * @return \Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * This is the reverse operation to {@link toHash()}. At least the hash
     * format generated by {@link toHash()} must be converted in reverse.
     * Additional formats might be supported in the rare case that this is
     * necessary. See the class description for more details on a hash format.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function fromHash($hash)
    {
        if (!is_array($hash)) {
            return new Value();
        }

        $selectionIdentifiers = array();
        foreach ($hash as $hashItem) {
            if (!is_string($hashItem)) {
                continue;
            }

            $selectionIdentifiers[] = $hashItem;
        }

        return new Value($selectionIdentifiers);
    }

    /**
     * Converts the given $value into a plain hash format.
     *
     * Converts the given $value into a plain hash format, which can be used to
     * transfer the value through plain text formats, e.g. XML, which do not
     * support complex structures like objects. See the class level doc block
     * for additional information. See the class description for more details on a hash format.
     *
     * @param \eZ\Publish\SPI\FieldType\Value|\Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return $value->identifiers;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \eZ\Publish\SPI\FieldType\Value|\Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            array(
                'data' => null,
                'externalData' => $this->toHash($value),
                'sortKey' => $this->getSortInfo($value),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return $this->fromHash($fieldValue->externalData);
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * Default implementation, which performs a "==" check with the value
     * returned by {@link getEmptyValue()}. Overwrite in the specific field
     * type, if necessary.
     *
     * @param \eZ\Publish\SPI\FieldType\Value|\Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        return $value === null || $value->identifiers === $this->getEmptyValue()->identifiers;
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = array();
        if (!is_array($fieldSettings)) {
            $validationErrors[] = new ValidationError('Field settings must be in form of an array');

            return $validationErrors;
        }

        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    "'%setting%' setting is unknown",
                    null,
                    array(
                        '%setting%' => $name,
                    )
                );
                continue;
            }

            switch ($name) {
                case 'options':
                    if (!is_array($value)) {
                        $validationErrors[] = new ValidationError(
                            "'%setting%' setting value must be of array type",
                            null,
                            array(
                                '%setting%' => $name,
                            )
                        );
                    } else {
                        foreach ($value as $option) {
                            if (!isset($option['name'])) {
                                $validationErrors[] = new ValidationError(
                                    "'%setting%' setting value item must have a 'name' property",
                                    null,
                                    array(
                                        '%setting%' => $name,
                                    )
                                );
                            } else {
                                if (!is_string($option['name'])) {
                                    $validationErrors[] = new ValidationError(
                                        "'%setting%' setting value item's 'name' property must be of string value",
                                        null,
                                        array(
                                            '%setting%' => $name,
                                        )
                                    );
                                }

                                if (empty($option['name'])) {
                                    $validationErrors[] = new ValidationError(
                                        "'%setting%' setting value item's 'name' property must have a value",
                                        null,
                                        array(
                                            '%setting%' => $name,
                                        )
                                    );
                                }
                            }

                            if (!isset($option['identifier'])) {
                                $validationErrors[] = new ValidationError(
                                    "'%setting%' setting value item must have an 'identifier' property",
                                    null,
                                    array(
                                        '%setting%' => $name,
                                    )
                                );
                            } else {
                                if (!is_string($option['identifier'])) {
                                    $validationErrors[] = new ValidationError(
                                        "'%setting%' setting value item's 'identifier' property must be of string value",
                                        null,
                                        array(
                                            '%setting%' => $name,
                                        )
                                    );
                                }

                                if (empty($option['identifier'])) {
                                    $validationErrors[] = new ValidationError(
                                        "'%setting%' setting value item's 'identifier' property must have a value",
                                        null,
                                        array(
                                            '%setting%' => $name,
                                        )
                                    );
                                }
                            }

                            if (!isset($option['priority'])) {
                                $validationErrors[] = new ValidationError(
                                    "'%setting%' setting value item must have an 'priority' property",
                                    null,
                                    array(
                                        '%setting%' => $name,
                                    )
                                );
                            } else {
                                if (!is_int($option['priority'])) {
                                    $validationErrors[] = new ValidationError(
                                        "'%setting%' setting value item's 'priority' property must be of integer value",
                                        null,
                                        array(
                                            '%setting%' => $name,
                                        )
                                    );
                                }
                            }
                        }
                    }

                    break;
                case 'isMultiple':
                    if (!is_bool($value)) {
                        $validationErrors[] = new ValidationError(
                            "'%setting%' setting value must be of boolean type",
                            null,
                            array(
                                '%setting%' => $name,
                            )
                        );
                    }
                    break;
                case 'delimiter':
                    if (!is_string($value)) {
                        $validationErrors[] = new ValidationError(
                            "'%setting%' setting value must be of string type",
                            null,
                            array(
                                '%setting%' => $name,
                            )
                        );
                    }
                    break;
                case 'query':
                    if (!is_string($value)) {
                        $validationErrors[] = new ValidationError(
                            "'%setting%' setting value must be of string type",
                            null,
                            array(
                                '%setting%' => $name,
                            )
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * If given $inputValue could not be converted or is already an instance of dedicate value object,
     * the method should simply return it.
     *
     * This is an operation method for {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return mixed The potentially converted input value
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = new Value(array($inputValue));
        } elseif (is_array($inputValue)) {
            foreach ($inputValue as $inputValueItem) {
                if (!is_string($inputValueItem)) {
                    return $inputValue;
                }
            }

            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * This is an operation method for {@see acceptValue()}.
     *
     *
     * @param \eZ\Publish\Core\FieldType\Value|\Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\Value $value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!is_array($value->identifiers)) {
            throw new InvalidArgumentType(
                '$value->identifiers',
                'array',
                $value->identifiers
            );
        }

        foreach ($value->identifiers as $identifier) {
            if (!is_string($identifier)) {
                throw new InvalidArgumentType(
                    $identifier,
                    Value::class,
                    $identifier
                );
            }
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * Return value is mixed. It should be something which is sensible for
     * sorting.
     *
     * It is up to the persistence implementation to handle those values.
     * Common string and integer values are safe.
     *
     * For the legacy storage it is up to the field converters to set this
     * value in either sort_key_string or sort_key_int.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return mixed
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }
}
