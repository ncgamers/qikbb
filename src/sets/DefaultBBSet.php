<?php
namespace qikbb\sets;

use qikbb\BBSet;
use qikbb\BBListStyle;
use qikbb\BBStyle;
use qikbb\validators\HexColorValidator;
use qikbb\validators\EmailValidator;
use qikbb\validators\UrlValidator;
use qikbb\visitors\NestLimitVisitor;

/**
 * Class DefaultBBSet
 *
 * Default style definitions.
 *
 * @package qikbb\sets
 */
class DefaultBBSet extends BBSet {
    /**
     * Construct the set
     */
    public function __construct() {
        $this->visitors = [
            new NestLimitVisitor(),
        ];

        // Validators that will be used
        $urlValidator = new UrlValidator(['message' => '"{value}" is not a valid URL.']);
        $emailValidator = new EmailValidator(['message' => '"{value}" is not a valid email.']);
        $colorValidator = new HexColorValidator();

        // Helpful lists that will be used
        $blockTags = [
            'h', 'code', 'quote', 'nlist', 'blist', 'spoiler', 'left', 'center', 'right'
        ];

        /*
         * Inline Tags
         */
        /*
         * Text format and styling */
        // Do not parse bb
        $this->styles[] = new BBSTyle('verbatim', '{PARAM}', ['noTags' => '*', 'autoClose' => true]);
        // Only valid hex colors can be used
        $this->styles[] = new BBStyle('color', '<span style="color:{ATTR}">{PARAM}</span>',
            ['validators' => ['attr' => [$colorValidator]]]);
        // Disallow self
        $this->styles[] = new BBStyle('b', '<span class="bold">{PARAM}</span>',
            ['noTags' => ['b']]);
        // Disallow self
        $this->styles[] = new BBStyle('i', '<span class="italic">{PARAM}</span>',
            ['noTags' => ['i']]);
        // Disallow self
        $this->styles[] = new BBStyle('u', '<span class="underline">{PARAM}</span>',
            ['noTags' => ['u']]);
        // Disallow self
        $this->styles[] = new BBStyle('s', '<span class="strike">{PARAM}</span>',
            ['noTags' => ['s']]);
        // Disallow self and block-style tags
        $config['noTags'] = array_merge($blockTags, ['sub', 'sup', 'img']);
        $this->styles[] = new BBStyle('sup', '<sup>{PARAM}</sup>', $config);
        $this->styles[] = new BBStyle('sub', '<sub>{PARAM}</sub>', $config);
        /*
         * Url and Links */
        /* Body Based */ // (Set no parse)
        $config = ['noTags' => '*', 'validators' => ['body' => [$urlValidator]]];
        /** @noinspection HtmlUnknownTarget */
        $this->styles[] = new BBStyle('img', '<img src="{PARAM}" />', $config);
        /** @noinspection HtmlUnknownTarget */
        $this->styles[] = new BBStyle('url', '<a href="{PARAM}" rel="nofollow">{PARAM}</a>', $config);
        // Validate input is an email
        $this->styles[] = new BBStyle('email', '<a href="mailto:{PARAM}" rel="nofollow">{PARAM}</a>',
            ['noTags' => '*', 'validators' => ['body' => [$emailValidator]]]);
        /* Attribute Based */
        /** @noinspection HtmlUnknownTarget */
        // Validate input as url and disallow block-style tags
        $this->styles[] = new BBStyle('url', '<a href="{ATTR}" rel="nofollow">{PARAM}</a>',
            ['noTags' => array_merge($blockTags, ['url']), 'validators' => ['attr' => [$urlValidator]]]);
        // Validate input as email and disallow block-style tags
        $this->styles[] = new BBStyle('email', '<a href="mailto:{ATTR}" rel="nofollow">{PARAM}</a>',
            ['noTags' => array_merge($blockTags, ['email']), 'validators' => ['attr' => [$emailValidator]]]);

        /*
         * Block Tags
         */
        // Disallow block-style tags
        $this->styles[] = new BBStyle('h', '<h3>{PARAM}</h3>',
            ['noTags' => $blockTags]);
        // Do not parse input
        $this->styles[] = new BBStyle('code', '<pre>{PARAM}</pre>',
            ['noTags' => '*', 'trim' => true]);
        /*
         * Text display and styling */
        // Trim the input
        $config = ['trim' => true];
        $this->styles[] = new BBStyle('left', '<div style="text-align:left">{PARAM}</div>', $config);
        $this->styles[] = new BBStyle('center', '<div style="text-align:center">{PARAM}</div>', $config);
        $this->styles[] = new BBStyle('right', '<div style="text-align:right">{PARAM}</div>', $config);
        /*
         * Functionality-es */
        // Limit the nesting on spoilers and quotes
        $config = ['nestLimit' => 4, 'trim' => true];
        $this->styles[] = new BBStyle('spoiler',
            '<div class="spoiler">' .
                /** @noinspection JSUnresolvedFunction */
                '<cite class="spoilerToggle" onclick="spoiler(this)">Click to open spoiler.</cite>' .
                '<p class="spoilerContent" style="display:none;">{PARAM}</p>' .
            '</div>', $config);
        $this->styles[] = new BBStyle('quote', '<blockquote class="quote">{PARAM}</blockquote>', $config);
        $this->styles[] = new BBStyle('quote', '<blockquote class="quote"><cite>{ATTR}</cite>{PARAM}</blockquote>', $config);
        /*
         * Lists */
        $this->styles[] = new BBListStyle('nlist', '', ['listTag' => 'ol']);
        $this->styles[] = new BBListStyle('blist', '', ['listTag' => 'ul']);
    }
}
