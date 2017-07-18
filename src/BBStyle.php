<?php
namespace qikbb;

/**
 * Class BBStyle
 *
 * A code definition to be used when parsing. Provides interfaces for validating BB input and
 * generating HTML output. When creating the style if [[noTags]] is set as '*' then internal parsing
 * will be disabled for the style.
 *
 * @package qikbb
 */
class BBStyle {
    /** @var string $tag */
    public $tag;
    /** @var array $noTags tags that are not allowed within the current tag */
    public $noTags = [];
    /**
     * Only really makes sense to set to true when parseContent == false. The tag will be
     * considered closed when found and will not output the BBCode even if no matching closer is
     * found.
     *
     * @var bool $autoClose */
    public $autoClose = false;
    /**
     * If set to false then anything after a an opening tag is found will be treated as plain text
     * until a closing tag is found or end of parse input. Using noTag == '*' is a shorthand method
     * of setting parseContent to false.
     *
     * @var bool $parseContent */
    public $parseContent = true;
     /** @var bool $trim if set to true then trim() will be called on the body of the tag. */
    public $trim = false;
     /** @var bool $hasAttribute Automatically set if {ATTR} is found in the replacement text. */
    public $hasAttribute = false;
    /** @var array $validators */
    public $validators;
    /** @var array $wrapper */
    protected $wrapper;
    /** @var array $properties Extraneous attributes, generally used to set visitor options */
    protected $properties = [];

    /**
     * @inheritdoc
     */
    public function __construct($tag, $text, $config = []) {
        $this->tag = $tag;
        $this->wrapper = explode('{PARAM}', $text);
        foreach ($this->wrapper as $wrap) {
            if (strpos($wrap, '{ATTR}') !== false) {
                $this->hasAttribute = true;
                break;
            }
        }

        foreach ($config as $attribute => $value) {
            $this->$attribute = $value;
        }

        if ($this->noTags === '*') {
            $this->parseContent = false;
            $this->noTags = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name) {
        return $this->properties[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Generate the begin text and end text.
     *
     * @param string  $attribute
     * @param string  $body
     * @param TagNode $tagNode
     * @param mixed   $error
     * @return array
     */
    public function create($attribute, $body, $tagNode, &$error) {
        if ($this->hasAttribute) {
            foreach ($this->wrapper as $wrap) {
                $wrapper[] = str_replace('{ATTR}', $attribute, $wrap);
            }
        }
        return implode($this->trim ? trim($body) : $body, $wrapper ?? $this->wrapper);
    }

    /**
     * Checks the validator array to determine if there are any present. Return an bool array with
     * the first index representing attribute validation and the second body validation.
     */
    public function hasValidators() {
        return [! empty($this->validators['attr']), ! empty($this->validators['body'])];
    }

    /**
     * Run an attribute validation on the input. Return true if passed.
     *
     * @param string $input
     * @param mixed  $error
     * @return bool
     */
    public function attrCheck($input, &$error) {
        $valid = true;
        foreach ($this->validators['attr'] as $validator) {
            $valid = $validator->validate($input, $error[]);
            if (! $valid) {
                break;
            }
        }
        return $valid;
    }

    /**
     * Run a body validation on the input. Return true if passed.
     *
     * @param string $input
     * @param mixed  $error
     * @return bool
     */
    public function bodyCheck($input, &$error) {
        $valid = true;
        foreach ($this->validators['body'] as $validator) {
            $valid = $validator->validate($input, $error[]);
            if (! $valid) {
                break;
            }
        }
        return $valid;
    }
}
