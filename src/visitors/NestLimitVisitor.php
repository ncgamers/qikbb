<?php
namespace qikbb\visitors;

use qikbb\TagNode;
use qikbb\Visitor;

/**
 * Class NestLimitVisitor
 *
 * Limits nesting of BBTags so that over-nested elements are converted to plain text. If the nest
 * limit is one (1) then it is more efficient to use the 'noTag' attribute when building the style.
 * @see BBSyle
 * @see BBSet
 *
 * @package qikbb\visitors
 */
class NestLimitVisitor extends Visitor {
    /** @var array $tagList */
    private $tagList;

    /**
     * @inheritdoc
     */
    public function visitRootNode($root) {
        // Visit children
        foreach ($root->getChildren() as $child) {
            $child->accept($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function visitTagNode($tag, $definition) {
        // If the tag is not closed or if nest limit is not set then continue with the children
        if ($tag->getClosed() !== TagNode::CLOSED || is_null($definition->nestLimit)) {
            foreach ($tag->getChildren() as $child) {
                $child->accept($this);
            }
            return;
        }

        // Visitor applies. Retrieve the proper tagName to check against
        if (isset($definition->nestGroup)) {
            sort($definition->nestGroup);
            $tagName = implode(':', $definition->nestGroup);
        } else {
            $tagName = $definition->tag;
        }

        // Increment current tag count
        $this->tagList[$tagName] = ($this->tagList[$tagName] ?? 0) + 1;

        // Check for over-nesting
        if ($this->tagList[$tagName] > $definition->nestLimit) {
            $this->engine->addError($tagName, '[' . $tagName . '] cannot be nested more than '
                . $definition->nestLimit . ' times.');
            $tag->setClosed(TagNode::PARTIAL);
        }

        // Check the children
        foreach ($tag->getChildren() as $child) {
            $child->accept($this);
        }

        // Decrement current tag number
        --$this->tagList[$tagName];
    }

    /**
     * @inheritdoc
     */
    public function visitTextNode($text) {
        // Do nothing
    }
}
