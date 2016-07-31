<?php
namespace qikbb;

/**
 * Class Node
 *
 * Representation of a parse tree node. Defines expectant behavior of nodes within the tree such as
 * generating output or accepting visitors.
 *
 * @package qikbb
 */
abstract class Node {
    /**
     * Returns plain text contained within the node.
     *
     * @return string
     */
    abstract public function getText();

    /**
     * Returns HTML contained within the node.
     *
     * @return string
     */
    abstract public function getHTML();

    /**
     * Accepts the provided visitor.
     *
     * @param Visitor $visitor
     */
    abstract public function accept($visitor);
}
