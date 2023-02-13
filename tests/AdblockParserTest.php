<?php

declare(strict_types=1);

namespace Limonte\Tests;

use Limonte\AdblockParser;
use Limonte\NotAnUrlException;

class AdblockParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException Exception
     */
    public function testInvalidUrl(): void
    {
        $this->expectException(NotAnUrlException::class);
        $this->parser = new AdblockParser();
        $this->shouldBlock(['sfsaf']);
    }

    public function testBlockByAddressParts(): void
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

    public function testBlockByDomainName(): void
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

        $this->parser = new AdblockParser(['|http://baddomain.example/']);
        $this->shouldBlock([
            'http://baddomain.example/banner.gif',
        ]);
        $this->shouldNotBlock([
            'http://gooddomain.example/analyze?http://baddomain.example',
        ]);
    }

    public function testBlockExactAddress(): void
    {
        $this->parser = new AdblockParser(['|http://example.com/|']);
        $this->shouldBlock([
            'http://example.com/',
        ]);
        $this->shouldNotBlock([
            'http://example.com/foo.gif',
            'http://example.info/redirect/http://example.com/',
        ]);
    }

    public function testBlockBeginningDomain(): void
    {
        $this->parser = new AdblockParser(['||example.com/banner.gif']);
        $this->shouldBlock([
            'http://example.com/banner.gif',
            'https://example.com/banner.gif',
            'http://www.example.com/banner.gif',
        ]);
        $this->shouldNotBlock([
            'http://badexample.com/banner.gif',
            'http://gooddomain.example/analyze?http://example.com/banner.gif',
        ]);
    }

    public function testCaretSeparator(): void
    {
        $this->parser = new AdblockParser(['http://example.com^']);
        $this->shouldBlock([
            'http://example.com/',
            'http://example.com:8000/ ',
        ]);
        $this->shouldNotBlock([
            'http://example.com.ar/',
        ]);

        $this->parser = new AdblockParser(['^example.com^']);
        $this->shouldBlock([
            'http://example.com:8000/foo.bar?a=12&b=%D1%82%D0%B5%D1%81%D1%82',
        ]);

        $this->parser = new AdblockParser(['^%D1%82%D0%B5%D1%81%D1%82^']);
        $this->shouldBlock([
            'http://example.com:8000/foo.bar?a=12&b=%D1%82%D0%B5%D1%81%D1%82',
        ]);

        $this->parser = new AdblockParser(['^foo.bar^']);
        $this->shouldBlock([
            'http://example.com:8000/foo.bar?a=12&b=%D1%82%D0%B5%D1%81%D1%82',
        ]);
    }

    public function testParserException(): void
    {
        $this->parser = new AdblockParser(['adv', '@@advice.']);
        $this->shouldBlock([
            'http://example.com/advert.html',
        ]);
        $this->shouldNotBlock([
            'http://example.com/advice.html',
        ]);

        $this->parser = new AdblockParser(['@@|http://example.com', '@@advice.', 'adv', '!foo']);
        $this->shouldBlock([
            'http://examples.com/advert.html',
        ]);
        $this->shouldNotBlock([
            'http://example.com/advice.html',
            'http://example.com/advert.html',
            'http://examples.com/advice.html',
            'http://examples.com/#!foo',
        ]);
    }

    public function testLoadRulesLocally(): void
    {
        $this->parser = new AdblockParser();
        $this->parser->loadRulesFromPath(__DIR__ . '/test-rules.txt');
        $this->assertSame(4, count($this->parser->getRules()));
        $this->shouldBlock([
            'http://example.com/avantlink/123',
            'http://example.com//avmws_asd.js',
        ]);
        $this->shouldNotBlock(['http://example.com//avmws_exception.js']);
    }

    public function testLoadRemoteRules(): void
    {
        $this->parser = new AdblockParser();
        $this->assertSame(AdblockParser::ONE_DAY_IN_SECONDS, $this->parser->getCacheExpireInSeconds());
        $this->parser->setCacheFolder(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache');
        $this->parser->clearCache();
        $glob = $this->parser->getCacheFolder() . '*';
        $this->assertSame(0, count(glob($glob)));
        $this->parser->loadRulesFromPaths([
            'https://raw.githubusercontent.com/easylist/easylist/master/easylist_adult/adult_adservers.txt',
            'https://raw.githubusercontent.com/easylist/easylist/master/easyprivacy/easyprivacy_trackingservers.txt',
        ]);
        $this->assertSame(2, count(glob($glob)));
        $this->parser->loadRulesFromPaths([
            'https://raw.githubusercontent.com/easylist/easylist/master/easylist_adult/adult_adservers.txt',
        ]);

        $this->parser->clearCache();
        $this->assertSame(0, count(glob($glob)));

        $this->parser->setCacheExpireInSeconds(0);

        $this->shouldBlock(['http://00px.net/']);
    }

    public function testLoadArrayOfResources(): void
    {
        $this->parser = new AdblockParser();
        $this->parser->loadRulesFromPaths([
            'https://raw.githubusercontent.com/easylist/easylist/master/easylist/easylist_general_block.txt',
            'https://raw.githubusercontent.com/easylist/easylist/master/easyprivacy/easyprivacy_trackingservers.txt',
        ]);

        $this->shouldBlock(['https://hello.com-ad-300x600-']); // rule from the first resource
        $this->shouldBlock(['http://00px.net/']); // rule from the second resource
    }

    /**
     * @param array<string> $url
     */
    private function shouldBlock(array $urls): void
    {
        foreach ($urls as $url) {
            $this->assertTrue($this->parser->shouldBlock($url), $url);
        }
    }

    /**
     * @param array<string> $urls
     */
    private function shouldNotBlock(array $urls): void
    {
        foreach ($urls as $url) {
            $this->assertFalse($this->parser->shouldBlock($url), $url);
        }
    }
}
