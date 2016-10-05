<?php
namespace Limonte\Tests;

use Limonte\AdblockParser;
use Limonte\AdblockRule;

class AdblockParserTest extends \PHPUnit_Framework_TestCase
{
    public function testOk()
    {
        $adblockParser = new AdblockParser([
            '||abs.proxistore.com^'
        ]);

        $this->assertTrue($adblockParser->shouldBlock('http://abs.proxistore.com'));
    }

    public function testToRegex()
    {
        $adblockParser = new AdblockRule('/slashes should be trimmed/');
        $this->assertEquals('slashes should be trimmed', $adblockParser->toRegex());
    }
}
