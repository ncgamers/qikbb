<?php
namespace qikbb\tests;

use PHPUnit_Framework_TestCase as TestCase;
use qikbb\sets\DefaultBBSet;
use qikbb\Engine;

/**
 * Class BBTestBase
 *
 * Base class for providing setup and wrapper function.
 *
 * @package qikbb\tests
 */
class BBTestBase extends TestCase {
    /** @var \qikbb\Engine $engine */
    protected static $engine;

    /**
     * Create a Engine parser object to be used.
     */
    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$engine = new Engine(new DefaultBBSet());
    }

    /**
     * Wrapper for basic assertion.
     *
     * @param string $text
     * @param string $html
     */
    protected function produces($text, $html) {
        $result = self::$engine->parse($text);
        $this->assertSame($html, $result);
    }
}
