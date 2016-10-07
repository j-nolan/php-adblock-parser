<?php
namespace Limonte\Tests;

use Limonte\AdblockParser;

class AdblockParserTest extends \PHPUnit_Framework_TestCase
{
    public function testBlockByAddressParts()
    {

        $this->parser = new AdblockParser(['/banner/*/img^']);
        $this->shouldBlock([
            'http://example.com/banner/foo/img',
            'http://example.com/banner/foo/bar/img?param',
            'http://example.com/banner//img/foo',
        ]);
        $this->shouldNotBlock([
            'http://example.com/banner/img',
            'http://example.com/banner/foo/imgraph',
            'http://example.com/banner/foo/img.gif',
        ]);
    }

    public function testBlockByDomainName()
    {
        $this->parser = new AdblockParser(['||ads.example.com^']);
        $this->shouldBlock([
            'http://ads.example.com/foo.gif',
            'http://server1.ads.example.com/foo.gif',
            'https://ads.example.com:8000/',
        ]);
        $this->shouldNotBlock([
            'http://ads.example.com.ua/foo.gif',
            'http://example.com/redirect/http://ads.example.com/',
        ]);
    }

    public function testLoadRulesLocally()
    {
        $this->parser = new AdblockParser;
        $this->parser->loadRules(__DIR__ . '/test-rules.txt');
        $this->assertEquals(3, count($this->parser->getRules()));
        $this->shouldBlock('http://example.com/avantlink/123');
        $this->shouldBlock('http://example.com//avmws_asd.js');
    }

    public function testLoadRemoteRules()
    {
        $this->parser = new AdblockParser;
        $this->parser->loadRules(
            'https://raw.githubusercontent.com/easylist/easylist/master/easylistfanboy/other/adult-addon.txt'
        );
        $this->shouldBlock('http://dot.wp.pl/');
    }

    public function testLoadArrayOfResources()
    {
        $this->parser = new AdblockParser;
        $this->parser->loadRules([
            'https://raw.githubusercontent.com/easylist/easylist/master/easylistfanboy/other/adult-addon.txt',
            'https://raw.githubusercontent.com/easylist/easylist/master/easylistfanboy/other/tracking-intl.txt',
        ]);

        $this->shouldBlock('http://rek.www.wp.pl');    // rule from the first resource
        $this->shouldBlock('http://webcount.finn.no'); // rule from the second resource
    }

    private function shouldBlock($url)
    {
        if (is_string($url)) {
            $this->assertTrue($this->parser->shouldBlock($url), $url);
        } elseif (is_array($url)) {
            foreach ($url as $i) {
                $this->assertTrue($this->parser->shouldBlock($i), $i);
            }
        }
    }

    private function shouldNotBlock($url)
    {
        if (is_string($url)) {
            $this->assertTrue($this->parser->shouldNotBlock($url), $url);
        } elseif (is_array($url)) {
            foreach ($url as $i) {
                $this->assertTrue($this->parser->shouldNotBlock($i), $i);
            }
        }
    }
}
