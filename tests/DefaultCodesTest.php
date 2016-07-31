<?php
namespace qikbb\tests;

/**
 * Class DefaultCodesTest
 *
 * Test that the default style codes are parsed correctly.
 *
 * @package qikbb\tests
 */
class DefaultCodesTest extends BBTestBase {
    /**
     * Test [b]
     */
    public function testBold() {
        $this->produces('[b]bold[/b]', '<span class="bold">bold</span>');
    }

    /**
     * Test [i]
     */
    public function testItalic() {
        $this->produces('[i]italic[/i]', '<span class="italic">italic</span>');
    }

    /**
     * Test [u]
     */
    public function testUnderline() {
        $this->produces('[u]underline[/u]', '<span class="underline">underline</span>');
    }

    /**
     * Test [s]
     */
    public function testStrike() {
        $this->produces('[s]strike[/s]', '<span class="strike">strike</span>');
    }

    /**
     * Test [h]
     */
    public function testHead() {
        $this->produces('[h]header[/h]', '<h3>header</h3>');
    }

    /**
     * Test [url=http://google.com]
     */
    public function testAttrUrl() {
        $this->produces('[url=http://google.com]google[/url]',
            '<a href="http://google.com" rel="nofollow">google</a>');
    }

    /**
     * Test [url]
     */
    public function testUrl() {
        $this->produces('[url]http://google.com[/url]',
            '<a href="http://google.com" rel="nofollow">http://google.com</a>');
    }

    /**
     * Test [email=ncg@ncga.me]
     */
    public function testAttrEmail() {
        $this->produces('[email=ncg@ncga.me]ncg[/email]',
            '<a href="mailto:ncg@ncga.me" rel="nofollow">ncg</a>');
    }

    /**
     * Test [email]
     */
    public function testEmail() {
        $this->produces('[email]ncg@ncga.me[/email]',
            '<a href="mailto:ncg@ncga.me" rel="nofollow">ncg@ncga.me</a>');
    }

    /**
     * Test [img]
     */
    public function testImg() {
        $this->produces('[img]http://ncga.me/img.jpg[/img]', '<img src="http://ncga.me/img.jpg" />');
    }

    /**
     * Test [code]
     */
    public function testCode() {
        $this->produces('[code]code[/code]', '<pre>code</pre>');
    }

    /**
     * Test [sup]
     */
    public function testSup() {
        $this->produces('[sup]super[/sup]', '<sup>super</sup>');
    }

    /**
     * Test [sub]
     */
    public function testSub() {
        $this->produces('[sub]sub[/sub]', '<sub>sub</sub>');
    }

    /**
     * Test [left]
     */
    public function testLeft() {
        $this->produces('[left]justleft[/left]', '<div style="text-align:left">justleft</div>');
    }

    /**
     * Test [center]
     */
    public function testCenter() {
        $this->produces('[center]justcenter[/center]', '<div style="text-align:center">justcenter</div>');
    }

    /**
     * Test [right]
     */
    public function testRight() {
        $this->produces('[right]justright[/right]', '<div style="text-align:right">justright</div>');
    }

    /**
     * Test [spoiler]
     */
    public function testSpoiler() {
        $this->produces('[spoiler]spoil[/spoiler]',
            '<div class="spoiler"><cite class="spoilerToggle" onclick="spoiler(this)">' .
            'Click to open spoiler.</cite><p class="spoilerContent" style="display:none;">' .
            'spoil</p></div>');
    }

    /**
     * Test [color=#fff]
     */
    public function test3Color() {
        $this->produces('[color=#fff]fff[/color]', '<span style="color:#fff">fff</span>');
    }

    /**
     * Test [color=#ffffff]
     */
    public function test6Color() {
        $this->produces('[color=#ffffff]ffffff[/color]', '<span style="color:#ffffff">ffffff</span>');
    }

    /**
     * Test [nlist]
     */
    public function testNlist() {
        $this->produces('[nlist][*]One[*]Two[/nlist]', '<ol><li>One</li><li>Two</li></ol>');
    }

    /**
     * Test [blist]
     */
    public function testBlist() {
        $this->produces('[blist][*]One[*]Two[/blist]', '<ul><li>One</li><li>Two</li></ul>');
    }

    /**
     * Test an example block of text.
     */
    public function testExample() {
        $text = 'The default codes include: [b]bold[/b], [i]italics[/i], [u]underlining[/u], '
            . '[url=http://jbbcode.com]links[/url], [color=#ddd]color![/color] and more.';
        $html = 'The default codes include: <span class="bold">bold</span>, <span class="italic">'
            . 'italics</span>, <span class="underline">underlining</span>, <a href="http://jbbcode.com"'
            . ' rel="nofollow">links</a>, <span style="color:#ddd">color!</span> and more.';
        $this->produces($text, $html);
    }
}
