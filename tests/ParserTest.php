<?php

declare(strict_types=1);

namespace App\Tests;

use App\AdblockParser\Parser;
use App\AdblockParser\NotAnUrlException;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @expectedException Exception
     */
    public function testInvalidUrl(): void
    {
        $this->expectException(NotAnUrlException::class);
        $this->parser = new Parser();
        $this->shouldBlock(['sfsaf']);
    }

    public function testBlockByAddressParts(): void
    {
        $this->parser = new Parser(['/banner/*/img^']);
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
        $this->parser = new Parser(['||ads.example.com^']);
        $this->shouldBlock([
            'http://ads.example.com/foo.gif',
            'http://server1.ads.example.com/foo.gif',
            'https://ads.example.com:8000/',
        ]);
        $this->shouldNotBlock([
            'http://ads.example.com.ua/foo.gif',
            'http://example.com/redirect/http://ads.example.com/',
        ]);

        $this->parser = new Parser(['|http://baddomain.example/']);
        $this->shouldBlock([
            'http://baddomain.example/banner.gif',
        ]);
        $this->shouldNotBlock([
            'http://gooddomain.example/analyze?http://baddomain.example',
        ]);
    }

    public function testBlockExactAddress(): void
    {
        $this->parser = new Parser(['|http://example.com/|']);
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
        $this->parser = new Parser(['||example.com/banner.gif']);
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
        $this->parser = new Parser(['http://example.com^']);
        $this->shouldBlock([
            'http://example.com/',
            'http://example.com:8000/ ',
        ]);
        $this->shouldNotBlock([
            'http://example.com.ar/',
        ]);

        $this->parser = new Parser(['^example.com^']);
        $this->shouldBlock([
            'http://example.com:8000/foo.bar?a=12&b=%D1%82%D0%B5%D1%81%D1%82',
        ]);

        $this->parser = new Parser(['^%D1%82%D0%B5%D1%81%D1%82^']);
        $this->shouldBlock([
            'http://example.com:8000/foo.bar?a=12&b=%D1%82%D0%B5%D1%81%D1%82',
        ]);

        $this->parser = new Parser(['^foo.bar^']);
        $this->shouldBlock([
            'http://example.com:8000/foo.bar?a=12&b=%D1%82%D0%B5%D1%81%D1%82',
        ]);
    }

    public function testParserException(): void
    {
        $this->parser = new Parser(['adv', '@@advice.']);
        $this->shouldBlock([
            'http://example.com/advert.html',
        ]);
        $this->shouldNotBlock([
            'http://example.com/advice.html',
        ]);

        $this->parser = new Parser(['@@|http://example.com', '@@advice.', 'adv', '!foo']);
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
