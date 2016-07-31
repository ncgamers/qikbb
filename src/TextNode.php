<?php
namespace qikbb;

/**
 * Class TextNode
 *
 * Representation of plain text within the parse tree.
 *
 * @package qikbb
 */
class TextNode extends Node {
    /** @var string $text */
    public $text = '';

    /**
     * @inheritdoc
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function getHTML() {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function accept($visitor) {
        $visitor->visitTextNode($this);
    }

    /**
     * Check if the TextNode is empty.
     *
     * @return bool
     */
    public function empty() {
        return empty($this->text);
    }

}
