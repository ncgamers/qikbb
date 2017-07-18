<?php
namespace qikbb\tests;

/**
 * Class ParseListTest
 *
 * Additional testing for nlist and blist
 *
 * @package qikbb\tests
 */
class ParseListTest extends BBTestBase {
    /**
     * An empty list should be treated as plain text.
     */
    public function testEmptyList() {
        $this->produces('[nlist][/nlist]', '[nlist][/nlist]');
        $this->assertSame(['Empty tag found. Treated as plain text.' => 1], self::$engine->getError('nlist'));
    }
    /**
     * If no list element delimiters are found then treat as plain text.
     */
    public function testNoDelimiterList() {
        $this->produces('[nlist]This doesn\'t have a delimiter[/nlist]', '[nlist]This doesn\'t have a delimiter[/nlist]');
        $this->assertSame(['Empty tag found. Treated as plain text.' => 1], self::$engine->getError('nlist'));
    }

    /**
     * Plain text before a delimiter is ignored and added as an error.
     */
    public function testPlainTestBeforeDelimiter() {
        $this->produces('[nlist]Plain Text [*]One[*]Two[/nlist]', '<ol><li>One</li><li>Two</li></ol>');
        $this->assertSame(["The following has been removed due to improper list nesting:\n"
            . 'Plain Text ' => 1], self::$engine->getError('nlist'));
    }

    /**
     * Delimiter inside of a tag should be ignored.
     */
    public function testDelimiterInTag() {
        $this->produces('[nlist][*]One[*]Two[b][*]Three[/b][/nlist]',
            '<ol><li>One</li><li>Two<span class="bold">[*]Three</span></li></ol>');
    }

    /**
     * If a delimiter causes an empty list element it is removed.
     */
    public function testEmptyListElement() {
        $this->produces('[nlist][*]One[*][*]Two[/nlist]', '<ol><li>One</li><li>Two</li></ol>');
    }

    /**
     * Plain text trailing an external tag should be appended to the current list element.
     */
    public function testPlainAfterTag() {
        $this->produces('[nlist][*]Before[b]Bold[/b]After[/nlist]',
            '<ol><li>Before<span class="bold">Bold</span>After</li></ol>');
    }

    /**
     * A delimiter after an external tag should create a new list element.
     */
    public function testDelimiterAfterTag() {
        $this->produces('[nlist][*]Before[b]Bold[/b][*]New[/nlist]',
            '<ol><li>Before<span class="bold">Bold</span></li><li>New</li></ol>');
    }

    /**
     * A delimiter following plain text after an external tag should append and create.
     */
    public function testPlainDelimiterAfterTag() {
        $this->produces('[nlist][*]Before[b]Bold[/b]After[*]New[/nlist]',
            '<ol><li>Before<span class="bold">Bold</span>After</li><li>New</li></ol>');
    }

    /**
     * Delimiters after external tags should continue to create new list elements.
     */
    public function testPlainDoubleDelimiterAfterTag() {
        $this->produces('[nlist][*]Before[b]Bold[/b]After[*]New[*]Line[/nlist]',
            '<ol><li>Before<span class="bold">Bold</span>After</li><li>New</li><li>Line</li></ol>');
    }
}
