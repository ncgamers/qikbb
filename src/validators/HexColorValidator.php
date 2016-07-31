<?php
namespace qikbb\validators;

/**
 * Class HexColorValidator
 *
 * Accept only colors that are valid hexdecimal.
 *
 * @package qikbb\validators
 */
class HexColorValidator extends Validator {
    /**
     * @inheritdoc
     */
    public function validateValue($value) {
        return preg_match('/^#(?:[a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $value) ? null : ['"{value}" is not a valid hex color.', []];
    }
}
