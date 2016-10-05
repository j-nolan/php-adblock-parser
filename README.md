PHP parser for Adblock Plus filters
===================================

[![Build Status](https://semaphoreci.com/api/v1/limonte/php-adblock-parser/branches/master/badge.svg)](https://semaphoreci.com/limonte/php-adblock-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/limonte/php-adblock-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/limonte/php-adblock-parser/?branch=master)

Usage
-----

To learn about Adblock Plus filter syntax check these links:

* https://adblockplus.org/en/filter-cheatsheet
* https://adblockplus.org/en/filters

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
