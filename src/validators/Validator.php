<?php
namespace qikbb\validators;

/**
 * Original Source from yiisoft/yii2 "Validator"
 * yii2 is licensed under BSD
 * @link http://github.com/yiisoft/yii2
 */

/**
 * Class Validator
 *
 * Base validator.
 *
 * @package qikbb\validators
 */
abstract class Validator {

    /**
     * Construct the validator
     */
    public function __construct($config = []) {
        foreach ($config as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    /**
     * Any additional init.
     */
    public function init() {}

    /**
     * Validates a given value.
     * You may use this method to validate a value out of the context of a data model.
     * @param mixed $value the data value to be validated.
     * @param string $error the error message to be returned, if the validation fails.
     * @return boolean whether the data is valid.
     */
    public function validate($value, &$error = null) {
        $result = $this->validateValue($value);
        if (empty($result)) {
            return true;
        }

        list($message, $params) = $result;
        if (is_array($value)) {
            $params['value'] = 'array()';
        } elseif (is_object($value)) {
            $params['value'] = 'object';
        } else {
            $params['value'] = $value;
        }

        foreach ($params as $key => $value) {
            $toSearch['{' . $key . '}'] = $value;
        }

        $error = strtr($message, $toSearch);
        return false;
    }

    /**
     * Validate a value. Return null if everything is good. Else return [$message, $params]
     *
     * @return null|array
     */
    abstract protected function validateValue($value);
}
