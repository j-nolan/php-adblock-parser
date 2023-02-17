<?php

namespace App\Tests;

use App\Kernel;
use App\AdblockParser\Parser;
use App\AdblockParser\ParserFactory;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParserFactoryTest extends KernelTestCase
{
    public function testCreateAdblockParser(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $adblockParserFactory = $kernel->getContainer()->get(ParserFactory::class);
        assert($adblockParserFactory instanceof ParserFactory);

        $adblockParser = $adblockParserFactory->createAdblockParserFromFiles([__DIR__ . '/test-rules.txt']);

        $this->assertCount(1, $adblockParser->getRuleCollections());
        $this->assertCount(
            2,
            $adblockParser->getRuleCollections()[Parser::DOMAIN_AGNOSTIC_IDENTIFIER]->getBlockers(),
        );

        Assert::assertTrue($adblockParser->shouldBlock('http://example.com/avantlink/123'));
        Assert::assertTrue($adblockParser->shouldBlock('http://example.com//avmws_asd.js'));
        Assert::assertFalse($adblockParser->shouldBlock('http://example.com//avmws_exception.js'));
    }

    public function testCreateAdblockParserFromFiles()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $adblockParserFactory = $kernel->getContainer()->get(ParserFactory::class);
        assert($adblockParserFactory instanceof ParserFactory);
        $adblockParser = $adblockParserFactory->createAdblockParserFromFiles([__DIR__ . '/test-rules.txt']);

        Assert::assertCount(1, $adblockParser->getRuleCollections());
        Assert::assertCount(
            2,
            $adblockParser->getRuleCollections()[Parser::DOMAIN_AGNOSTIC_IDENTIFIER]->getBlockers(),
        );
        Assert::assertCount(
            1,
            $adblockParser->getRuleCollections()[Parser::DOMAIN_AGNOSTIC_IDENTIFIER]->getExceptions(),
        );
    }

    public function testLoadCachedAdblockParser()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $adblockParserFactory = $kernel->getContainer()->get(ParserFactory::class);
        assert($adblockParserFactory instanceof ParserFactory);

        $adblockParserFactory->clearCachedAdblockParser();

        $sleepDurationMs = 50;
        $adBlockParserToCreate = new Parser(['test']);
        $fakeAdblockerParserCreator = static function() use ($sleepDurationMs, $adBlockParserToCreate) {
            usleep($sleepDurationMs * 1000);
            return $adBlockParserToCreate;
        };
        $storeStartTimeMs = 1000 * microtime();
        $adblockParserCreated = $adblockParserFactory->loadCachedAdblockParser($fakeAdblockerParserCreator);
        $storeEndTimeMs = 1000 * microtime();

        Assert::assertCount(1, $adblockParserCreated->getAllRules());
        Assert::assertGreaterThanOrEqual($sleepDurationMs, $storeEndTimeMs - $storeStartTimeMs);

        $loadStartTimeMs = 1000 * microtime();
        $adblockParserCreated = $adblockParserFactory->loadCachedAdblockParser($fakeAdblockerParserCreator);
        $loadEndTimeMs = 1000 * microtime();

        Assert::assertCount(1, $adblockParserCreated->getAllRules());
        Assert::assertLessThanOrEqual($sleepDurationMs, $loadEndTimeMs - $loadStartTimeMs);
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}