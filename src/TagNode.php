<?php
namespace qikbb;

/**
 * Class TagNode
 *
 * Represents a tag node in the tree.
 *
 * @package qikbb
 */
class TagNode extends ParentNode {
    /** @const int OPEN */
    const OPEN = 1;
    /** @const int PARTIAL */
    const PARTIAL = 2;
    /** @const int CLOSED */
    const CLOSED = 4;

    /** @var ParentNode $parent */
    private $parent;
    /** @var Engine $engine */
    private $engine;
    /** @var BBStyle $definition */
    private $definition;
    /**
     * Defines the inputs that were used to generate the node. The plain text tag as well as the
     * parsed out attribute (if it exists).
     *
     * @var array $inputs */
    private $inputs;
    /** @var bool $closed */
    private $closed = self::OPEN;

    /**
     * Open a new tag node. Prepare a TextNode as the first child so that text can be added.
     *
     * @param ParentNode $parent
     * @param Engine     $engine
     * @param BBStyle    $definition
     * @param string     $tag
     * @param string     $attribute
     */
    public function __construct($parent, $engine, $definition, $tag, $attribute) {
        $this->parent = $parent;
        $this->engine = $engine;
        $this->definition = $definition;
        $this->inputs = [$tag, $attribute];
        $this->curText = $this->children[] = new TextNode($this);

        if ($definition->autoClose) {
            $this->closed = self::CLOSED;
        }
    }

    /**
     * API function for visitors to set whether the tag should be closed or not.
     *
     * @param int $close
     * @throws \InvalidArgumentException
     */
    public function setClosed($close) {
        switch($close) {
            case self::OPEN:
            case self::PARTIAL:
            case self::CLOSED:
                $this->closed = $close;
                break;
            default:
                throw new \InvalidArgumentException('setClosed() must use a TagNode class constant as an argument.');
        }
    }

    /**
     * API function for visitors to retrieve current closed state.
     *
     * @return int
     */
    public function getClosed() {
        return $this->closed;
    }

    /**
     * API function for visitors to retrieve the definition.
     *
     * @return BBStyle
     */
    public function getStyle() {
        return $this->definition;
    }

    /**
     * @inheritdoc
     */
    public function getText() {
        return $this->inputs[0] . $this->getBodyText()
            . ($this->closed >= self::PARTIAL ? '[/' . $this->definition->tag . ']' : '');
    }

    /** @noinspection PhpInconsistentReturnPointsInspection
     * @inheritdoc
     */
    public function getHTML() {
        $html = '';
        foreach ($this->children as $child) {
            $html .= $child->getHTML();
        }

        switch ($this->closed) {
            case self::OPEN:
                return $this->inputs[0] . $html;
            case self::PARTIAL:
                return $this->inputs[0] . $html . '[/' . $this->definition->tag . ']';
            case self::CLOSED:
                $error = null;
                $return = $this->definition->create($this->inputs[1], $html, $this, $error);
                if (! empty($error)) {
                    $this->engine->addError($this->definition->tag, $error);
                }
                return $return;
        }
    }

    /**
     * @inheritdoc
     */
    public function accept($visitor) {
        $visitor->visitTagNode($this, $this->definition);
    }

    /**
     * @inheritdoc
     */
    public function getStackInput() {
        return $this->parent->getStackInput() . $this->inputs[0];
    }

    /**
     * A matching closer has been found for this node. However, the node may not be valid. Check the
     * validators on the definition in order to determine if the node should be closed.
     */
    public function close() {
        list($hasAttrCheck, $hasBodyCheck) = $this->definition->hasValidators();

        $bodyError = $attrError = null;
        $close = true;
        if ($hasAttrCheck) {
            $close = $this->definition->attrCheck($this->inputs[1], $attrError);
        }

        if ($close && $hasBodyCheck) {
            $close = $this->definition->bodyCheck($this->getBodyText(), $bodyError);
        }

        if ($close) {
            $this->closed = self::CLOSED;
        } else {
            $this->engine->addError($this->definition->tag, array_merge($attrError ?? [], $bodyError ?? []));
            $this->closed = self::PARTIAL;
        }
    }

    /**
     * Retrieve plain text from the children.
     */
    private function getBodyText() {
        $text = '';
        foreach ($this->children as $child) {
            $text .= $child->getText();
        }
        return $text;
    }
}
