<?php
namespace Limonte\Tests;

use Limonte\AdblockParser;

class AdblockParserTest extends \PHPUnit_Framework_TestCase
{
    public function testOk()
    {
        $adblockParser = new AdblockParser([
            '||abs.proxistore.com^'
        ]);

        $this->assertTrue($adblockParser->shouldBlock('http://abs.proxistore.com'));
    }
}
