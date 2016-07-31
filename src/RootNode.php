<?php
namespace qikbb;

/**
 * Class RootNode
 *
 * The top node of the tree.
 *
 * @package qikbb
 */
class RootNode extends ParentNode {
    /** @var TextNode $curText */
    public $curText;

    /**
     * Prepare a TextNode as the first child so that text can be added.
     */
    public function __construct() {
        $this->curText = $this->children[] = new TextNode($this);
    }

    /**
     * @inheritdoc
     */
    public function getText() {
        $text = '';
        foreach ($this->children as $child) {
            $text .= $child->getText();
        }
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function getHTML() {
        $html = '';
        foreach ($this->children as $child) {
            $html .= $child->getHTML();
        }
        return $html;
    }

    /**
     * @inheritdoc
     */
    public function accept($visitor) {
        $visitor->visitRootNode($this);
    }

    /**
     * @inheritdoc
     */
    public function getStackInput() {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function close() {}
}
