<?php
namespace qikbb\tests;

/**
 * Class ParseContentTest
 *
 * Check that tag properties with parseContent = false and / or showTag = false are behaving
 * correctly.
 *
 * @package qikbb\tests
 */
class ParseContentTest extends BBTestBase {
    /**
     * Verify that when a style is created with parseContent = false the contents are not parsed.
     */
    public function testSimpleNoParse() {
        $this->produces('[verbatim][b]not bold[/b][/verbatim]', '[b]not bold[/b]');
    }

    /**
     * Check that no parse does not create extra characters with buffer text.
     */
    public function testBufferNoParse() {
        $this->produces('buffer text[verbatim]this should [b]not[/b] be bold[/verbatim]buffer text',
            'buffer textthis should [b]not[/b] be boldbuffer text');
    }

    /**
     * Ensure that when an open parseContent = false and showTag = false tag does not close
     * everything is considered plain text and the tag does not show up.
     */
    public function testNoCloseNoParseNoShow() {
        $this->produces('This should [verbatim]not be [b]parsed, because[/b] there is no end.',
            'This should not be [b]parsed, because[/b] there is no end.');
    }

    /**
     * Test immediate end after a no parse no show tag.
     */
    public function testNoParseNoShowEnd() {
        $this->produces('[verbatim]', '');
    }

    /**
     * Ensure that when an open parseContent = false and showTag = false tag does not close
     * everything is considered plain text and the tag shows up
     */
    public function testNoCloseNoParse() {
        $this->produces('This should [code]not be [b]parsed, because[/b] there is no end.',
            'This should [code]not be [b]parsed, because[/b] there is no end.');
    }

    /**
     * Test immediate end after a no parse no show tag.
     */
    public function testNoParseEnd() {
        $this->produces('[code]', '[code]');
    }
}
