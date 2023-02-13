<?php

declare(strict_types=1);

namespace Limonte;

class AdblockRuleCollection
{
    /** @var list<AdblockRule> $exceptions */
    private array $exceptions = [];

    /** @var list<AdblockRule> $blockers */
    private array $blockers = [];

    public function addRule(AdblockRule $rule, bool $dropComments = true): void
    {
        if ($dropComments && ($rule->isComment() || $rule->isHtml())) {
            return;
        }
        if ($rule->isException()) {
            $this->exceptions[] = $rule;
        } else {
            $this->blockers[] = $rule;
        }
    }

    /**
     * @return list<AdblockRule>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return list<AdblockRule>
     */
    public function getBlockers(): array
    {
        return $this->blockers;
    }
}