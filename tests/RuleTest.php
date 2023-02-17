<?php

namespace App\Tests;

use App\AdblockParser\Rule;
use App\AdblockParser\InvalidRuleException;

class RuleTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRegex()
    {
        $rule = new Rule('/slashes should be trimmed/');
        $this->assertEquals('slashes should be trimmed', $rule->getRegex());
    }

    public function testInvalidRegex()
    {
        $this->expectException(InvalidRuleException::class);
        $invalidRule = new Rule('//');
        $invalidRule->getRegex();
    }

    public function testEscapeSpecialCharacters()
    {
        $rule = new Rule('.$+?{}()[]/\\');
        $this->assertEquals('\.\$\+\?\{\}\(\)\[\]\/\\\\', $rule->getRegex());
    }

    public function testCaret()
    {
        $rule = new Rule('domain^');
        $this->assertEquals('domain([^\w\d_\-.%]|$)', $rule->getRegex());
    }

    public function testAsterisk()
    {
        $rule = new Rule('domain*');
        $this->assertEquals('domain.*', $rule->getRegex());
    }

    public function testVerticalBars()
    {
        $rule = new Rule('||domain');
        $this->assertEquals('^([^:\/?#]+:)?(\/\/([^\/?#]*\.)?)?domain', $rule->getRegex());

        $rule = new Rule('|domain');
        $this->assertEquals('^domain', $rule->getRegex());

        $rule = new Rule('domain|bl||ah');
        $this->assertEquals('domain\|bl\|\|ah', $rule->getRegex());
    }

    public function testMatchUrl()
    {
        $rule = new Rule('swf|');
        $this->assertTrue($rule->matchUrl("http://example.com/annoyingflash.swf"));
        $this->assertFalse($rule->matchUrl("http://example.com/swf/index.html"));
    }

    public function testComment()
    {
        $rule = new Rule('!this is comment');
        $this->assertTrue($rule->isComment());
        $rule = new Rule('[Adblock Plus 1.1]');
        $this->assertTrue($rule->isComment());
        $rule = new Rule('non-comment rule');
        $this->assertFalse($rule->isComment());
    }

    public function testRegistrableDomain(): void
    {
        $rule = new Rule('/banThisPath.');
        $this->assertNull($rule->getRegistrableDomain());
        $rule = new Rule('||domain.com');
        $this->assertSame('domain.com', $rule->getRegistrableDomain());
        $rule = new Rule('||domain.com/aPath');
        $this->assertSame('domain.com', $rule->getRegistrableDomain());
        $rule = new Rule('||subdomain.domain.com^*/aPath');
        $this->assertSame('domain.com', $rule->getRegistrableDomain());
        $rule = new Rule('@@||subdomain.domain.com^*/aPath');
        $this->assertSame('domain.com', $rule->getRegistrableDomain());
    }
}
