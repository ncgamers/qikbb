<?php
namespace qikbb\tests;

/**
 * Class BBValidatorsTest
 *
 * Test the validators are working correctly.
 *
 * @package qikbb\tests
 */
class BBValidatorsTest extends BBTestBase {
    /**
     * Test [email]
     */
    public function testEmail() {
        $this->produces('[email]" onclick="alert(\'hello\')" ncg@ncga.me[/email]',
            '[email]" onclick="alert(\'hello\')" ncg@ncga.me[/email]');
        $this->assertSame(['"" onclick="alert(\'hello\')" ncg@ncga.me" is not a valid email.' => 1],
            self::$engine->getError('email'));
    }
}
