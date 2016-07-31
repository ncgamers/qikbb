<?php
namespace qikbb\tests;

/**
 * Class ParseNestingTest
 *
 * Ensure that disallowed tags cannot be nested and that nest limits are enforced. A lot of these
 * assertions are probably redundant.
 *
 * @package qikbb\tests
 */
class ParseNestingTest extends BBTestBase {
    /** @var array $blockTags */
    protected static $blockTags = [
        'h', 'code', 'quote', 'spoiler', 'left', 'center', 'right',
    ];
    /** @var array $allTags */
    protected static $allTags = [
        'b', 'i', 'u', 's', 'sub', 'sup', 'img', 'url', 'email', 'h', 'code', 'quote',
        'spoiler', 'left', 'center', 'right',
    ];
    protected static $allTagsInput = [
        'img' => 'http://google.com',
        'email' => 'ncgamers@ncgamers.org',
    ];
    /** @var array $allTagsOutput */
    protected static $allTagsOutput;

    /**
     * Create a Engine parser object to be used.
     */
    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        foreach (self::$allTags as $tag) {
            if (isset(self::$allTagsInput[$tag])) {
                self::$allTagsInput[$tag]
                    = '[' . $tag . ']' . self::$allTagsInput[$tag] . '[/' . $tag . ']';
            } else {
                self::$allTagsInput[$tag] = '[' . $tag . ']' . $tag . '[/' . $tag . ']';
            }

            self::$allTagsOutput[$tag] = self::$engine->parse(self::$allTagsInput[$tag]);
        }

    }

    /**
     * [b] tag should disallow [b]
     */
    public function testDisallowB() {
        list($d, $a) = $this->disallowAllow(['b']);
        $this->check($d, $a, 'b', ['<span class="bold">', '</span>']);
    }

    /**
     * [i] tag should disallow [i]
     */
    public function testDisallowI() {
        list($d, $a) = $this->disallowAllow(['i']);
        $this->check($d, $a, 'i', ['<span class="italic">', '</span>']);
    }

    /**
     * [s] tag should disallow [s]
     */
    public function testDisallowS() {
        list($d, $a) = $this->disallowAllow(['s']);
        $this->check($d, $a, 's', ['<span class="strike">', '</span>']);
    }

    /**
     * [sub] tag should disallow $blockTags [sub][sup][img]
     */
    public function testDisallowSub() {
        list($d, $a) = $this->disallowAllow(array_merge(self::$blockTags, ['sub', 'sup', 'img']));
        $this->check($d, $a, 'sub', ['<sub>', '</sub>']);
    }

    /**
     * [sup] tag should disallow $blockTags [sup][sub][img]
     */
    public function testDisallowSup() {
        list($d, $a) = $this->disallowAllow(array_merge(self::$blockTags, ['sup', 'sub', 'img']));
        $this->check($d, $a, 'sup', ['<sup>', '</sup>']);
    }

    /**
     * [url=] tag should disallow $blockTags [url]
     */
    public function testDisallowUrl_A() {
        list($d, $a) = $this->disallowAllow(array_merge(self::$blockTags, ['url']));
        $this->check($d, $a, ['url', 'http://google.com'], ['<a href="http://google.com" rel="nofollow">', '</a>']);
    }

    /**
     * [email=] tag should disallow $blockTags [email]
     */
    public function testDisallowEmail_A() {
        list($d, $a) = $this->disallowAllow(array_merge(self::$blockTags, ['email']));
        $this->check($d, $a, ['email', 'ncg@ncgamers.org'], ['<a href="mailto:ncg@ncgamers.org" rel="nofollow">', '</a>']);
    }

    /**
     * [h] tag should disallow $blockTags [h]
     */
    public function testDisallowH() {
        list($d, $a) = $this->disallowAllow(array_merge(self::$blockTags, ['h']));
        $this->check($d, $a, 'h', ['<h3>', '</h3>']);
    }

    /**
     * [quote] tag should disallow null
     */
    public function testDisallowQuote() {
        list($d, $a) = $this->disallowAllow([]);
        $this->check($d, $a, 'quote', ['<blockquote class="quote">', '</blockquote>']);
    }

    /**
     * [quote=] tag should disallow null
     */
    public function testDisallowQuote_A() {
        list($d, $a) = $this->disallowAllow([]);
        $this->check($d, $a, ['quote', 'Frrz'], ['<blockquote class="quote"><cite>Frrz</cite>', '</blockquote>']);
    }

    /**
     * [spoiler] tag should disallow null
     */
    public function testDisallowSpoiler() {
        $wrap = [
            '<div class="spoiler">' .
            /** @noinspection JSUnresolvedFunction */
            '<cite class="spoilerToggle" onclick="spoiler(this)">Click to open spoiler.</cite>' .
            '<p class="spoilerContent" style="display:none;">', '</p></div>'];

        list($d, $a) = $this->disallowAllow([]);
        $this->check($d, $a, 'spoiler', $wrap);
    }

    /**
     * [left] tag should disallow null
     */
    public function testDisallowLeft() {
        list($d, $a) = $this->disallowAllow([]);
        $this->check($d, $a, 'left', ['<div style="text-align:left">', '</div>']);
    }

    /**
     * [center] tag should disallow null
     */
    public function testDisallowCenter() {
        list($d, $a) = $this->disallowAllow([]);
        $this->check($d, $a, 'center', ['<div style="text-align:center">', '</div>']);
    }

    /**
     * [right] tag should disallow null
     */
    public function testDisallowRight() {
        list($d, $a) = $this->disallowAllow([]);
        $this->check($d, $a, 'right', ['<div style="text-align:right">', '</div>']);
    }

    /**
     * [spoiler] can be nested at most 4 times
     */
    public function testMaxNestSpoiler() {
        $wrap = [
            '<div class="spoiler">' .
            /** @noinspection JSUnresolvedFunction */
            '<cite class="spoilerToggle" onclick="spoiler(this)">Click to open spoiler.</cite>' .
            '<p class="spoilerContent" style="display:none;">', '</p></div>'];

        $in = '[spoiler][spoiler][spoiler][spoiler][spoiler][spoiler]text[/spoiler][/spoiler][/spoiler][/spoiler][/spoiler][/spoiler]';
        $out = implode(implode(implode(implode('[spoiler][spoiler]text[/spoiler][/spoiler]', $wrap), $wrap), $wrap), $wrap);

        $this->produces($in, $out);
        $this->assertSame(['[spoiler] cannot be nested more than 4 times.' => 2], self::$engine->getError('spoiler'));
    }

    /**
     * [quote] can be nested at most 4 times
     */
    public function testMaxNestQuote() {
        $wrap = ['<blockquote class="quote">', '</blockquote>'];
        $wrapA = ['<blockquote class="quote"><cite>Frrz</cite>', '</blockquote>'];

        $in = '[quote][quote=Frrz][quote][quote=Frrz][quote][quote=Frrz]text[/quote][/quote][/quote][/quote][/quote][/quote]';
        $out = implode(implode(implode(implode('[quote][quote=Frrz]text[/quote][/quote]', $wrapA), $wrap), $wrapA), $wrap);

        $this->produces($in, $out);
        $this->assertSame(['[quote] cannot be nested more than 4 times.' => 2], self::$engine->getError('quote'));
    }

    /**
     * Improperly nested tags should be tracked for closers, even though they don't close. This is
     * so that any valid internal tags will not be cut off.
     */
    public function testImproperNestOrder() {
        $this->produces('[b]stuff[b]more[color=#fff]things[/b][/color][/b]',
            '<span class="bold">stuff[b]more<span style="color:#fff">things[/b]</span></span>');
    }

    /**
     * Check that disallowed tags are inherited from the stack.
     */
    public function testNestedDisallow() {
        $this->produces('[b][quote][url=http://google.com][b]link[/b][/url][/quote][/b]',
            '<span class="bold"><blockquote class="quote"><a href="http://google.com" rel="nofollow">'
            . '[b]link[/b]</a></blockquote></span>');
        $this->assertSame(['[b] cannot be used within [b][quote][url=http://google.com]' => 1],
            self::$engine->getError('Invalid Tag Placement'));
    }

    /**
     * Check allowed and disallowed tags using two arrays.
     *
     * @param array  $d
     * @param array  $a
     * @param string $tag
     * @param array  $wrap
     * @param bool   $error
     */
    private function check($d, $a, $tag, $wrap, $error = true) {
        if (is_array($tag)) {
            $tagWrap = ['[' . $tag[0] . '=' . $tag[1] . ']', '[/' . $tag[0] . ']'];
        } else {
            $tagWrap = ['[' . $tag . ']', '[/' . $tag . ']'];
        }

        foreach ($d as $c) {
            $this->produces(implode(self::$allTagsInput[$c], $tagWrap),
                implode(self::$allTagsInput[$c], $wrap));
            if ($error) {
                $this->assertSame(
                    ['[' . $c . '] cannot be used within ' . $tagWrap[0] => 1],
                    self::$engine->getError('Invalid Tag Placement'));
            }
        }

        foreach ($a as $c) {
            $this->produces(implode(self::$allTagsInput[$c], $tagWrap),
                implode(self::$allTagsOutput[$c], $wrap));
            $this->assertSame(false, self::$engine->getError('Invalid Tag Placement'));
        }
    }

    /**
     * Create the allow and disallow arrays from the disallowed input.
     *
     * @param array $disallowed
     * @return array
     */
    private function disallowAllow($disallowed) {
        return [$disallowed, array_diff(self::$allTags, $disallowed)];
    }
}
