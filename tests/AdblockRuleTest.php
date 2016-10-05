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
}
