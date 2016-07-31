<?php
namespace qikbb;

/**
 * Class ParentNode
 *
 * Represents a node that can act as a parent. Provides basic parental methods such as adding new
 * node elements.
 *
 * @package qikbb
 */
abstract class ParentNode extends Node {
    /** @var TextNode $curText */
    public $curText;
    /** @var Node[] $children */
    protected $children;

    /**
     * Adds a child element to the node.
     *
     * @param Engine  $engine
     * @param BBStyle $definition
     * @param string  $tag
     * @param string  $attribute
     * @return TagNode
     */
    public function addElement($engine, $definition, $tag, $attribute) {
        if ($this->curText->empty()) {
            array_pop($this->children);
        } else {
            $this->curText = new TextNode();
        }

        $ret = $this->children[] = new TagNode($this, $engine, $definition, $tag, $attribute);
        $this->children[] = $this->curText;

        return $ret;
    }

    /**
     * API function for visitors to retrieve node children.
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Retrieve the input used to create the node.
     *
     * @return string
     */
    abstract public function getStackInput();

    /**
     * Called when the node should be wrapped up.
     */
    abstract public function close();
}
