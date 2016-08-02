# qikbb
Another PHP BBCode implementation.

Acknowledgements
----------------
Firstly, the overall structure and code is heavily based on [jbowens/jBBCode](https://github.com/jbowens/jBBCode),
which is licensed under MIT. Be sure to check out their repository.

Secondly, the validators are taken almost directly from [yiisoft/yii2](https://github.com/yiisoft/yii2) validators. Yii2 is
licensed under BSD. Be sure to check out their repository.


About
-----
This isn't a professional project, it's just something I figured I'd share. I wanted something that was faster than
JBBCode. Of course, this is also at the expense of some of the features of JBBCode, such as auto-closing tags.
If you would like to contribute then open an issue or pull request and I'll take a look. However, my goal for the
project isn't to overload it with features. I wanted something quick and therefore cut down on things I didn't like.

As a simple baseline, in comparison to JBBCode, I've seen my implementation perform twice as fast on small strings
and up to five times as fast on longer strings.


PHP Version Support
-----
The code is currently implemented with a couple PHP 7 features, such as the null coalesce (??) operator. If there
is any interest in this code I'll create a PHP 5 branch that removes the PHP 7 features.

Additionally PCRE Regex is required to use this. However, it is only used to tokenize the parser input string (which
provides huge speed improvements over, for example, JBBCode's Tokenizer implementation). It is also used in the validators.
Furthermore, if you wish to have list tag support DOM is required (generally compiled by default).


Quick Documentation
-------------------
### Composer
Add the following to your composer.json
```
"require": {
    "ncgamers/qikbb": "1.0.*"
}
```

### PHP

```
// This assume that auto-loading is already done
use qikbb\Engine;
use qikbb\sets\DefaultBBSet;

$engine = new Engine(new DefaultBBSet());
echo $engine->parse('[b]bold[/b]');
// <span class="bold">bold</span>
```

##### Extra
If you need more information regarding composer see the [documentation](https://getcomposer.org/doc/00-intro.md).
