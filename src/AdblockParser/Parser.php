<?php

declare(strict_types=1);

namespace App\AdblockParser;

use App\AdblockParser\NotAnUrlException;
use Symfony\Contracts\Cache\CacheInterface;

class Parser
{
    public const DOMAIN_AGNOSTIC_IDENTIFIER = 'domain-agnostic';

    /** @var array<string,RuleCollection> */
    private array $ruleCollections;

    /** @param array<string> $rules */
    public function __construct(
        array $rules = [],
    ) {
        $this->ruleCollections = [];
        $this->addRules($rules);
    }

    /** @param array<string> $rules */
    public function addRules(array $rules): void
    {
        foreach ($rules as $rule) {
            try {
                $adblockRule = new Rule($rule);
                $domainIdentifier = $adblockRule->getRegistrableDomain() ?? self::DOMAIN_AGNOSTIC_IDENTIFIER;
                if (!isset($this->ruleCollections[$domainIdentifier])) {
                    $this->ruleCollections[$domainIdentifier] = new RuleCollection();
                }
                $this->ruleCollections[$domainIdentifier]->addRule(rule: $adblockRule);
            } catch (InvalidRuleException) {
                // Skip invalid rules
            }
        }
    }

    public function getRuleCollections(): array
    {
        return $this->ruleCollections;
    }

    /**
     * @return Rule[]
     */
    public function getAllRules(): array
    {
        $allRules = [];
        foreach ($this->ruleCollections as $ruleCollection) {
            $allRules[] = $ruleCollection->getAllRules();
        }

        return $allRules;
    }

    public function shouldBlock(string $url): bool
    {
        $url = trim($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new NotAnUrlException('Invalid URL');
        }

        $host = parse_url($url)['host'];
        if (!is_string($host)) {
            throw new NotAnUrlException('Invalid URL');
        }
        $registrableDomain = DomainParser::parseRegistrableDomain($host);

        foreach ($this->getRulesToApplyForDomain($registrableDomain) as $rule) {
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
     * @return list<Rule>
     */
    private function getRulesToApplyForDomain(string $registrableDomain): array
    {
        return array_merge( // exceptions must go first
            ($this->ruleCollections[self::DOMAIN_AGNOSTIC_IDENTIFIER] ?? null)?->getExceptions() ?? [],
            ($this->ruleCollections[$registrableDomain] ?? null)?->getExceptions() ?? [],
            ($this->ruleCollections[self::DOMAIN_AGNOSTIC_IDENTIFIER] ?? null)?->getBlockers() ?? [],
            ($this->ruleCollections[$registrableDomain] ?? null)?->getBlockers() ?? [],
        );
    }
}
