<?php
namespace Limonte\Tests;

use Limonte\AdblockParser;

class AdblockParserTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBlock()
    {
        $parser = new AdblockParser([
            '||abs.proxistore.com^'
        ]);

        $this->assertTrue($parser->shouldBlock('http://abs.proxistore.com'));
        $this->assertFalse($parser->shouldBlock('https://stackoverflow.com'));
    }

    public function testLoadRulesLocally()
    {
        $parser = new AdblockParser;
        $parser->loadRules(__DIR__ . '/test-rules.txt');
        $this->assertTrue($parser->shouldBlock('http://example.com/avantlink/123'));
        $this->assertTrue($parser->shouldBlock('http://example.com//avmws_asd.js'));
    }

    public function testLoadRemoteRules()
    {
        $parser = new AdblockParser;
        $parser->loadRules(
            'https://raw.githubusercontent.com/easylist/easylist/master/easylistfanboy/other/adult-addon.txt'
        );
        $this->assertTrue($parser->shouldBlock('http://dot.wp.pl/'));
    }

    public function testLoadArrayOfResources()
    {
        $parser = new AdblockParser;
        $parser->loadRules([
            'https://raw.githubusercontent.com/easylist/easylist/master/easylistfanboy/other/adult-addon.txt',
            'https://raw.githubusercontent.com/easylist/easylist/master/easylistfanboy/other/tracking-intl.txt',
        ]);

        $this->assertTrue($parser->shouldBlock('http://rek.www.wp.pl'));    // rule from the first resource
        $this->assertTrue($parser->shouldBlock('http://webcount.finn.no')); // rule from the second resource
    }
}
