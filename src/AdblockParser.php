<?php

declare(strict_types=1);

namespace Limonte;

class AdblockParser
{
    public const ONE_DAY_IN_SECONDS = 24 * 60 * 60;

    /** @var list<AdblockRule> */
    private array $rules;

    private ?string $cacheFolder = null;

    private int $cacheExpire = self::ONE_DAY_IN_SECONDS;

    /** @param array<string> $rules */
    public function __construct(array $rules = [])
    {
        $this->rules = [];
        $this->addRules($rules);
    }

    /** @param array<string> $rules */
    public function addRules(array $rules): void
    {
        foreach ($rules as $rule) {
            try {
                $this->rules[] = new AdblockRule($rule);
            } catch (InvalidRuleException) {
                // Skip invalid rules
            }
        }

        // Sort rules, exceptions first
        usort($this->rules, static function ($a, $b) {
            return (int) $b->isException() <=> (int) $a->isException();
        });
    }

    /** @param array $paths */
    public function loadRulesFromPaths(array $paths): void
    {
        foreach ($paths as $path) {
            $this->loadRulesFromPath($path);
        }
    }

    public function loadRulesFromPath(string $path): void
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $content = $this->getCachedResource($path);
        } else {
            $content = @file_get_contents($path);
        }
        if ($content) {
            $rules = preg_split("/(\r\n|\n|\r)/", $content);
            $this->addRules($rules);
        }
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function shouldBlock(string $url): bool
    {
        $url = trim($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new NotAnUrlException('Invalid URL');
        }

        foreach ($this->rules as $rule) {
            if ($rule->isComment() || $rule->isHtml()) {
                continue;
            }

            if ($rule->matchUrl($url)) {
                return !$rule->isException();
            }
        }

        return false;
    }

    /**
     * Get cache folder.
     */
    public function getCacheFolder(): ?string
    {
        return $this->cacheFolder;
    }

    /**
     * Set cache folder.
     */
    public function setCacheFolder(string $cacheFolder): void
    {
        $this->cacheFolder = rtrim($cacheFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get cache expire.
     */
    public function getCacheExpireInSeconds(): int
    {
        return $this->cacheExpire;
    }

    /**
     * Set cache expire.
     */
    public function setCacheExpireInSeconds(int $expireInSeconds): void
    {
        $this->cacheExpire = $expireInSeconds;
    }

    /**
     * Clear external resources cache.
     */
    public function clearCache(): void
    {
        if ($this->cacheFolder) {
            foreach (glob($this->cacheFolder . '*') as $file) {
                unlink($file);
            }
        }
    }

    private function getCachedResource(string $url): ?string
    {
        if (!$this->cacheFolder) {
            return @file_get_contents($url) ?: null;
        }

        $cacheFile = $this->cacheFolder . basename($url) . md5($url);

        if (file_exists($cacheFile) && (filemtime($cacheFile) > (time() - $this->cacheExpire))) {
            // Don't bother refreshing, just use the file as-is.
            $content = @file_get_contents($cacheFile);
        } else {
            // Our cache is out-of-date, so load the data from our remote server,
            // and also save it over our cache for next time.
            $content = @file_get_contents($url);
            if ($content) {
                file_put_contents($cacheFile, $content, LOCK_EX);
            }
        }

        return $content ?: null;
    }
}
