<?php
namespace qikbb;

/**
 * Class BBListStyle
 *
 * Extension of BBStyle in order to support lists.
 *
 * @package qikbb
 */
class BBListStyle extends BBStyle {
    /** @var string $listTag */
    public $listTag;

    /**
     * @inheritdoc
     */
    public function create($attribute, $body, $tagNode, &$error) {
        /*
         * This is pretty hacky. Perhaps there is a better way to create the list and preserve tag
         * rules.
         */
        $dom = new \DOMDocument();
        $dom->loadXML('<tag>' . $body . '</tag>');

        $removed = "";
        $list = [];
        $listing = false;
        foreach ($dom->documentElement->childNodes as $child) {
            /*
             * Any text before the initial list delimiter, '[*]', should be ignored / removed. After
             * the first list delimiter is found all further text will either be appended or create
             * a new list element.
             */
            if (! $listing) {
                // No first delimiter found, treat as removed text or search if $child is DomText
                if ($child instanceof \DOMText && mb_strpos($child->nodeValue, '[*]', null) !== false) {
                    $list = explode('[*]', $child->nodeValue);

                    // Remove first element because it is before the delimiter
                    $removed .= array_shift($list);

                    $listing = true;
                    // Get last index for appending
                    end($list);
                    $last = key($list);
                } else {
                    $removed .= $dom->saveXML($child);
                }
            } else {
                /*
                 * First delimiter has been found. Check for additional delimiters or append to the
                 * last element.
                 */
                if ($child instanceof \DOMText) {
                    if (count($tmp = explode('[*]', $child->nodeValue)) === 1) {
                        $list[$last] .= $child->nodeValue;
                    } else {
                        $list = array_merge($list, $tmp);
                    }
                } else {
                    $list[$last] .= $dom->saveXML($child);
                }
            }
        }

        // Remove new lines from the ends of each list item
        foreach ($list as &$li) {
            $li = trim($li, "\n\r");
        }
        // Filter empty elements
        $list = array_filter($list);

        if (empty($list)) {
            // List is empty, treat as a partially closed node
            $error[] = 'Empty tag found. Treated as plain text.';
            return '[' . $this->tag . ']' . $body . '[/' . $this->tag . ']';
        }

        if (! empty($removed)) {
            $error[] = "The following has been removed due to improper list nesting:\n" . $removed;
        }

        // List as elements, create the html and return
        return '<' . $this->listTag . '><li>' . implode('</li><li>', $list)
            . '</li></' . $this->listTag . '>';
    }
}
