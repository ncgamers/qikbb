<?php
namespace qikbb;

/**
 * Class BBSet
 *
 * @package qikbb
 */
abstract class BBSet {
    /** @var BBStyle[] $styles */
    public $styles;
    /** @var Visitor[] $visitors */
    public $visitors = [];
}
