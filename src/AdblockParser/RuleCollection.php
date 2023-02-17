<?php

declare(strict_types=1);

namespace App\AdblockParser;

class RuleCollection
{
    /** @var list<Rule> $exceptions */
    private array $exceptions = [];

    /** @var list<Rule> $blockers */
    private array $blockers = [];

    public function addRule(Rule $rule): bool
    {
        if ($rule->isHtml() || $rule->isComment()) {
            return false;
        }
        if ($rule->isException()) {
            $this->exceptions[] = $rule;
        } else {
            $this->blockers[] = $rule;
        }

        return true;
    }

    /**
     * @return list<Rule>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return list<Rule>
     */
    public function getBlockers(): array
    {
        return $this->blockers;
    }

    /**
     * @return list<Rule>
     */
    public function getAllRules(): array
    {
        return array_merge($this->getExceptions(), $this->getBlockers());
    }
}