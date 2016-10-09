PHP parser for Adblock Plus filters
===================================

[![Build Status](https://travis-ci.org/limonte/php-adblock-parser.svg?branch=master)](https://travis-ci.org/limonte/php-adblock-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/limonte/php-adblock-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/limonte/php-adblock-parser/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/limonte/php-adblock-parser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/limonte/php-adblock-parser/?branch=master)

Usage
-----

To learn about Adblock Plus filter syntax check these links:

- https://adblockplus.org/en/filter-cheatsheet
- https://adblockplus.org/en/filters

1. Get filter rules somewhere: write them manually, read lines from a file
   downloaded from [EasyList](https://easylist.to/), etc.:

   ```php
   $rules = [
       "||ads.example.com^",
       "@@||ads.example.com/notbanner^$~script",
   ];
   ```

2. Create AdblockRules instance from the rules array:

   ```php
   use Limonte\AdblockParser;

   $adblockParser = AdblockParser($rules);
   $adblockParser->addRules($anotherRules);
   ```

3. Use this instance to check if an URL should be blocked or not:

   ```php
   $adblockParser->shouldBlock("http://ads.example.com"); // true
   $adblockParser->shouldBlock("http://non-ads.example.com"); // false
   ```

Related projects
----------------

- Python parser for Adblock Plus filters: [scrapinghub/adblockparser](https://github.com/scrapinghub/adblockparser/)
- EasyList filter subscription: [easylist/easylist](https://github.com/easylist/easylist/)
