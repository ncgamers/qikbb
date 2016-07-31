<?php
namespace qikbb;

/**
 * Class Engine
 *
 * A parser for BBCode. BBStyles can be configured to allow specific inputs and/or nesting
 * limitations. A BBSet is used as a wrapper for the BBStyles and is provided as an input to the
 * Engine in order to generate the output HTML. Any errors that occur during parsing can be accessed
 * with [[getError()]].
 *
 * @package qikbb
 */
class Engine {
    /** @var BBStyle[] $styles */
    private $styles;
    /** @var Visitor[] $visitors */
    private $visitors;
    /** @var string[] $tokens */
    private $tokens;
    /** @var string[] $nameStack */
    private $nameStack;
    /** @var array $nodeStack */
    private $nodeStack;
    /** @var ParentNode $node */
    private $node;
    /** @var RootNode $root */
    private $root;
    /** @var array $invalids */
    private $invalids;
    /** @var array $errors */
    private $errors;

    /**
     * Call the reset function in order to prepare for parsing.
     *
     * @param BBSet $set
     */
    public function __construct($set = null) {
        if (! empty($set)) {
            $this->setStyles($set);
        }
    }

    /**
     * Apply the styles in the style set.
     *
     * @param BBSet $set
     */
    public function setStyles($set) {
        foreach ($set->styles as $style) {
            $this->styles[$style->tag][$style->hasAttribute] = $style;
        }

        foreach ($set->visitors as $visitor) {
            $this->visitors[] = $visitor->setEngine($this);
        }
    }

    /**
     * Add an error for the specified tag.
     *
     * @param string       $tag
     * @param string|array $message
     */
    public function addError($tag, $message) {
        foreach ((array) $message as $text) {
            $this->errors[$tag][$text] = ($this->errors[$tag][$text] ?? 0) + 1;
        }
    }

    /**
     * Retrieve all errors or errors relating to a specific tag. Errors are in the format:
     * [
     *    'tag' => ['message' => count],
     * ]
     *
     * @param string $tag
     * @return array
     */
    public function getError($tag = null) {
        if (! isset($tag)) {
            return $this->errors ?: false;
        } else {
            return $this->errors[$tag] ?? false;
        }
    }


    /**
     * Parse through the provided string and convert the BBCode into proper markup based on the
     * styles previously defined.
     *
     * @param string $source
     * @return string
     */
    public function parse($source) {
        // Reset the parser before starting
        $this->reset();

        /*
         * Create the tokens. This is the only place that the parser (aside from validators) that
         * a regular expression is used. Preg_split is much, much faster than doing it in userland.
         */
        $this->tokens = preg_split('~([\[\]])~', $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $first = reset($this->tokens);

        while($first !== false) {
            if ('[' !== $first) {
                $this->node->curText->text .= $first;
            } else {
                /*
                 * Check if the found delimiter is followed by text. If it is not then it has
                 * occurred at the end of the string and no further processing is required.
                 */
                if (($second = next($this->tokens)) === false) {
                    $this->node->curText->text .= '[';
                    break;
                }

                /*
                 * There's potential for the following characters to also be opening delimiter. It's
                 * invalid to for openers to be within a tag so treat them as text.
                 */
                while ('[' === $second) {
                    $this->node->curText->text .= '[';

                    if (($second = next($this->tokens)) === false) {
                        $this->node->curText->text .= '[';
                        break 2;
                    }
                }

                /*
                 * Now, we have found an opening delimiter followed by text. If the next token is an
                 * opening delimiter then the previous items were plain text and checking should be
                 * restarted. If the next token is a closing delimiter parse the tag, otherwise
                 * treat everything as plain text.
                 */
                if (($third = next($this->tokens)) === '[') {
                    $this->node->curText->text .= '[' . $second;
                    continue;
                } else if ($third !== ']') {
                    $this->node->curText->text .= '[' . $second . $third;
                } else {
                    $this->parseTag($second);
                }
            }
            $first = next($this->tokens);
        }

        return $this->cleanup();
    }

    /**
     * Parse an opening or closing tag. If a closing tag completes an opening tag then add the node
     * to the tree.
     *
     * @param string $tag;
     */
    private function parseTag($tag) {
        /*
         * /* Todo: currently only supports single attribute
         *  *  Only supports single attributes, perhaps multiple attributes will be added in the
         *  *  future
         */
        $closer = $attribute = $hasAttribute = false;
        if ($tag[0] !== '/') {
            // Opening tag, get the name and attribute
            $name = strtok($tag, '=');
            $attribute = strtok('');
            $hasAttribute = $attribute !== false;
        } else if (mb_strlen($tag) === 1) {
            // Closing tag with no name, treat as plain text
            $this->node->curText->text .= '[/]';
            return;
        } else {
            // Closing tag
            $name = mb_substr($tag, 1);
            $closer = true;
        }

        // Check that an opener exists
        if ($closer && isset($this->styles[$name])) {
            if (! empty($k = array_keys($this->nameStack, $name))) {
                if (isset($this->invalids[$name]) && $this->invalids[$name] > 0) {
                    --$this->invalids[$name];
                    $this->node->curText->text .= '[' . $tag . ']';
                    return;
                }

                end($k);
                $idx = current($k);

                // Attempt to close the node.
                /** @noinspection PhpUndefinedMethodInspection */
                $this->nodeStack[$idx][0]->close();

                // Remove the rest of the stack
                array_splice($this->nodeStack, $idx);
                array_splice($this->nameStack, $idx);

                $idx1 = $idx - 1;
                $this->node = $this->nodeStack[$idx1][0];
                $this->nodeStack[$idx1][1] = array_intersect_key($this->invalids, $this->nodeStack[$idx1][1]);
                $this->invalids = &$this->nodeStack[$idx1][1];
            } else {
                // No opener is in the stack. Treat as plain text and move on.
                $this->node->curText->text .= '[' . $tag . ']';
            }
        } else if (isset($this->styles[$name][$hasAttribute])) {
            if (isset($this->invalids[$name])) {
                $this->addError('Invalid Tag Placement', '[' . $tag . '] cannot be used within '
                    . $this->node->getStackInput());
                ++$this->invalids[$name];
                $this->node->curText->text .= '[' . $tag . ']';
                return;
            }

            if ($this->styles[$name][$hasAttribute]->parseContent) {
                $this->nameStack[] = $name;
                $this->nodeStack[] = [
                    $this->node->addElement($this, $this->styles[$name][$hasAttribute], '[' . $tag . ']', $attribute),
                    array_merge($this->invalids, array_fill_keys($this->styles[$name][$hasAttribute]->noTags, 0)),
                ];

                $idx = count($this->nodeStack) - 1;
                $this->node = $this->nodeStack[$idx][0];
                $this->invalids = &$this->nodeStack[$idx][1];
            } else {
                $this->parseAsText(
                    $this->node->addElement($this, $this->styles[$name][$hasAttribute], '[' . $tag . ']', $attribute), $name);
            }
        } else {
            // All this work and it isn't actually a tag.
            $this->node->curText->text .= '[' . $tag . ']';
        }
    }

    /**
     * Parse the input as text until a closing tag has been found for the node.
     *
     * @param TagNode $node
     * @param string  $name
     */
    private function parseAsText($node, $name) {
        $closer = '/' . $name;
        if (($first = next($this->tokens)) === false) {
            return;
        }
        if (($second = next($this->tokens)) === false) {
            return;
        }
        if (($third = next($this->tokens)) === false) {
            return;
        }

        // Check that the inputs are [$closer]. If not then keep cycling until closed.
        while ($first !== '[' || $second !== $closer || $third !== ']') {
            $node->curText->text .= $first;

            $first = $second;
            $second = $third;
            if (($third = next($this->tokens)) === false) {
                $node->curText->text .= $first . $second;
                return;
            }
        }

        // Success!
        $node->close();
    }

    /**
     * Reset the engine to an initial state so that parsing may begin.
     */
    private function reset() {
        $this->errors = [];
        $this->nameStack = [''];
        $this->nodeStack = [[new RootNode(), []]];
        $this->node = $this->root = $this->nodeStack[0][0];
        $this->invalids = &$this->nodeStack[0][1];
    }

    /**
     * Do final cleanup before returning the parsed text. This includes running through the
     * visitors, checking for errors, and examining unclosed tags.
     *
     * @return string
     */
    private function cleanup() {
        // Go through the visitors
        foreach ($this->visitors as $visitor) {
            $this->root->accept($visitor);
        }

        // Add unclosed tag error
        if (count($this->nameStack) > 1) {
            $this->addError('Unclosed Tags', array_slice($this->nameStack, 1));
        }

        // Return the HTML
        return $this->root->getHTML();
    }
}
