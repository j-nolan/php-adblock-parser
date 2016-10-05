<?php
namespace Limonte\Tests;

use Limonte\AdblockRule;
use Limonte\InvalidRuleException;

class AdblockRuleTest extends \PHPUnit_Framework_TestCase
{
    public function testToRegex()
    {
        $adblockRule = new AdblockRule('/slashes should be trimmed/');
        $this->assertEquals('slashes should be trimmed', $adblockRule->toRegex());
    }

    /**
     * @expectedException Limonte\InvalidRuleException
     */
    public function testInvalidRegex()
    {
        $invalidRule = new AdblockRule('//');
        $invalidRule->toRegex();
    }

    public function testEscapeSpecialCharacters()
    {
        $adblockRule = new AdblockRule('.$+?{}()[]\\');
        $this->assertEquals('\.\$\+\?\{\}\(\)\[\]\\\\', $adblockRule->toRegex());
    }

    public function testCaret()
    {
        $adblockRule = new AdblockRule('domain^');
        $this->assertEquals('domain([^\w\d_\-.%]|$)', $adblockRule->toRegex());
    }

    public function testAsterisk()
    {
        $adblockRule = new AdblockRule('domain*');
        $this->assertEquals('domain.*', $adblockRule->toRegex());
    }

    public function testVerticalBars()
    {
        $adblockRule = new AdblockRule('||domain');
        $this->assertEquals('^(?:[^:/?#]+:)?(?://(?:[^/?#]*\.)?)?domain', $adblockRule->toRegex());

        $adblockRule = new AdblockRule('|domain');
        $this->assertEquals('^domain', $adblockRule->toRegex());

        $adblockRule = new AdblockRule('domain|bl||ah');
        $this->assertEquals('domain\|bl\|\|ah', $adblockRule->toRegex());
    }
}
