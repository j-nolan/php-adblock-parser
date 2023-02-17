<?php

declare(strict_types=1);

namespace App\AdblockParser;

use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ParserFactory
{
    private const ADBLOCK_PARSER_CACHE_KEY = 'AdblockParser';

    public function __construct(
        private CacheInterface $cache,
    )
    {
    }

    public function clearCachedAdblockParser(): void
    {
        $this->cache->delete(self::ADBLOCK_PARSER_CACHE_KEY);
    }

    /**
     * @param callable(): Parser $adblockParserCreator
     */
    public function loadCachedAdblockParser(callable $adblockParserCreator): Parser
    {
        return $this->cache->get(
            self::ADBLOCK_PARSER_CACHE_KEY,
            static function (CacheItemInterface $item) use ($adblockParserCreator) {
                return $adblockParserCreator();
            }
        );
    }

    /**
     * @param array<string> $paths
     * @throws NotAPathException
     */
    public function createAdblockParserFromFiles(array $paths)
    {
        $adblockParser = new Parser();
        foreach ($paths as $path) {
            $content = file_get_contents($path);
            if ($content === false) {
                throw new NotAPathException(
                    "The following string is not a valid path to a file $path"
                );
            }
            $lines = preg_split("/(\r\n|\n|\r)/", $content);
            $adblockParser->addRules($lines);
        }

        return $adblockParser;
    }
}
