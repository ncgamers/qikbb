<?php
namespace qikbb;

/**
 * Class VisitorInterface
 *
 * Implementation details for node visitors. Any visitors that are registered to the BBSet will be
 * passed to each node in the tree when a string is parsed by the Engine.
 *
 * @package qikbb
 */
abstract class Visitor {
    /** @var Engine $engine */
    protected $engine;

    /**
     * Apply any constraints to the provided RootNode.
     *
     * @param RootNode $root
     */
    abstract public function visitRootNode($root);

    /**
     * Apply any constraints to the provided TagNode.
     *
     * @param TagNode $tag
     * @param BBStyle $style
     */
    abstract public function visitTagNode($tag, $style);

    /**
     * Apply any constraints to the provided TestNode.
     *
     * @param TextNode $text
     */
    abstract public function visitTextNode($text);

    /**
     * Set the engine to the provided one. This allows, among other things, errors to be registered.
     *
     * @param Engine $engine
     * @return $this
     */
    public function setEngine($engine) {
        $this->engine = $engine;
        return $this;
    }
}
