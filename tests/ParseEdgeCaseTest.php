<?php
namespace qikbb\tests;

/**
 * Class ParseEdgeCaseTest
 *
 * Check the edge cases are handled properly.
 *
 * @package qikbb\tests
 */
class ParseEdgeCaseTest extends BBTestBase {
    /**
     * Test the empty string.
     */
    public function testEmptyString() {
        $this->produces('', '');
    }

    /**
     * Tests attempting to use a code that does not exist.
     */
    public function testNonexistentCodeMalformed() {
        $this->produces('[wat]', '[wat]');
    }

    /**
     * Tests attempting to use a code that does not exist, but this time in a well-formed fashion.
     *
     * @depends testNonexistentCodeMalformed
     */
    public function testNonexistentCodeWellformed() {
        $this->produces('[wat]something[/wat]', '[wat]something[/wat]');
    }

    /**
     * Tests a whole bunch of meaningless left brackets.
     */
    public function testAllLeftBrackets() {
        $this->produces('[[[[[[[[', '[[[[[[[[');
    }

    /**
     * Tests a whole bunch of meaningless right brackets.
     */
    public function testAllRightBrackets() {
        $this->produces(']]]]]', ']]]]]');
    }

    /**
     * Intermixes well-formed, meaningful tags with meaningless brackets.
     */
    public function testRandomBracketsInWellformedCode() {
        $this->produces('[b][[][[i]heh[/i][/b]',
            '<span class="bold">[[][<span class="italic">heh</span></span>');
    }

    /**
     * Tests a closed tag within a url tag.
     */
    public function tesClosedWithinClosed() {
        $this->produces('[url=http://jbbcode.com][b]oh yeah[/b][/url]',
            '<a href="http://jbbcode.com" rel="nofollow"><span class="bold">oh yeah</span></a>');
    }

    /**
     * Tests an unclosed tag within a closed tag.
     */
    public function testUnclosedWithinClosed() {
        $this->produces('[url=http://jbbcode.com][b]oh yeah[/url]',
            '<a href="http://jbbcode.com" rel="nofollow">[b]oh yeah</a>');
    }

    /**
     * Tests half completed opening tag.
     */
    public function testHalfOpenTag() {
        $this->produces('[b', '[b');
        $this->produces('wut [url=http://jbbcode.com', 'wut [url=http://jbbcode.com');
    }

    /**
     * Tests half completed closing tag.
     */
    public function testHalfClosingTag() {
        $this->produces('[b]this should be bold[/b', '[b]this should be bold[/b');
    }

    /**
     * Tests lots of left brackets before the actual tag. For example: [[[[[[[[b]bold![/b]
     */
    public function testLeftBracketsThenTag() {
        $this->produces('[[[[[b]bold![/b]', '[[[[<span class="bold">bold!</span>');
    }

    /**
     * Tests a whitespace after left bracket.
     */
    public function testWhitespaceAfterLeftBracketWhithoutTag() {
        $this->produces('[ ABC ] ', '[ ABC ] ');
    }

    /**
     * Test that a empty close tag produces plain text
     */
    public function testEmptyCloseTag() {
        $this->produces('[b]plaintext[/]', '[b]plaintext[/]');
    }

    /**
     * Test double open and close tags produce plain text.
     */
    public function testDoubleOpenAndClose() {
        $this->produces('[[]]', '[[]]');
        $this->produces('[[/]]', '[[/]]');
    }

    /**
     * Tests a valid tag inside of an invalid tag.
     */
    public function testValidInsideInvalid() {
        $this->produces('[b]bold[color=invalid]in-[u]underline[/u]-valid[/color]done[/b]',
            '<span class="bold">bold[color=invalid]in-<span class="underline">underline</span>-valid[/color]done</span>');
    }

    public function testBracketInTag() {
        $this->produces('[b]:-[[/b]', '<span class="bold">:-[</span>');
    }

    public function testBracketWithSpaceInTag() {
        $this->produces('[b]:-[ [/b]', '<span class="bold">:-[ </span>');
    }

    public function testBracketWithTextInTag() {
        $this->produces('[b]:-[ foobar[/b]', '<span class="bold">:-[ foobar</span>');
    }

    public function testMultibleBracketsWithTextInTag() {
        $this->produces('[b]:-[ [fo[o[bar[/b]', '<span class="bold">:-[ [fo[o[bar</span>');
    }

    public function testMultibleBracketsWithTextInTagOnRight() {
        $this->produces('[b]:-[bar][/b]', '<span class="bold">:-[bar]</span>');
    }

    public function testUnclosedTagError() {
        self::$engine->parse('[b][b][b]');
        $this->assertSame([
            'Invalid Tag Placement' => [
                '[b] cannot be used within [b]' => 2,
            ],
            'Unclosed Tags' => [
                'b' => 1,
            ],
        ], self::$engine->getError());
    }
}
