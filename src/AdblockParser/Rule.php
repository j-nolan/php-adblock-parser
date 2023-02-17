<?php

declare(strict_types=1);

namespace App\AdblockParser;

use App\AdblockParser\Str;

class Rule
{
    private string $rule;

    private ?string $regex = null;

    private bool $isComment = false;

    private bool $isHtml = false;

    private bool $isException = false;

    private ?string $registrableDomain = null; // when set, the rules applies only to this registrable domain

    public function __construct(string $rule)
    {
        $this->rule = $rule;

        if (Str::startsWith($this->rule, '@@')) {
            $this->isException = true;
            $this->rule = mb_substr($this->rule, 2);
        }

        if (preg_match(
            pattern: '/\|\|([^\^\/\?\#]*)/',
            subject: $this->rule,
            matches: $matches,
        )) {
            $domain = $matches[1];
            $this->registrableDomain = DomainParser::parseRegistrableDomain($domain);
        }

        // comment
        if (Str::startsWith($rule, '!') || Str::startsWith($rule, '[Adblock')) {
            $this->isComment = true;

        // HTML rule
        } elseif (Str::contains($rule, '##') || Str::contains($rule, '#@#')) {
            $this->isHtml = true;

        // URI rule
        } else {
            $this->makeRegex();
        }
    }

    public function matchUrl(string $url): bool
    {
        return (bool) preg_match(
            '/' . ($this->getRegex() ?? '') . '/',
            $url,
        );
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function isComment(): bool
    {
        return $this->isComment;
    }

    public function isHtml(): bool
    {
        return $this->isHtml;
    }

    public function isException(): bool
    {
        return $this->isException;
    }

    public function getRegistrableDomain(): ?string
    {
        return $this->registrableDomain;
    }

    private function makeRegex(): void
    {
        if (empty($this->rule)) {
            throw new InvalidRuleException('Empty rule');
        }

        $regex = $this->rule;

        // Check if the rule isn't already regexp
        if (Str::startsWith($regex, '/') && Str::endsWith($regex, '/')) {
            $regex = mb_substr($this->rule, 1, mb_strlen($this->rule) - 2);
            $regex = preg_replace('/\//', '\\\\/', $regex);
            $this->regex = $regex;

            if (empty($this->regex)) {
                throw new InvalidRuleException('Empty rule');
            }

            return;
        }

        // escape special regex characters
        $regex = preg_replace('/([\\\.\$\+\?\{\}\(\)\[\]\/])/', '\\\\$1', $this->rule);

        // Separator character ^ matches anything but a letter, a digit, or
        // one of the following: _ - . %. The end of the address is also
        // accepted as separator.
        $regex = str_replace('^', '([^\w\d_\-.%]|$)', $regex);

        // * symbol
        $regex = str_replace('*', '.*', $regex);

        // | in the end means the end of the address
        if (Str::endsWith($regex, '|')) {
            $regex = mb_substr($regex, 0, mb_strlen($regex) - 1) . '$';
        }

        // || in the beginning means beginning of the domain name
        if (Str::startsWith($regex, '||')) {
            if (mb_strlen($regex) > 2) {
                // http://tools.ietf.org/html/rfc3986#appendix-B
                $regex = '^([^:\/?#]+:)?(\/\/([^\/?#]*\.)?)?' . mb_substr($regex, 2);
            }
        // | in the beginning means start of the address
        } elseif (Str::startsWith($regex, '|')) {
            $regex = '^' . mb_substr($regex, 1);
        }

        // other | symbols should be escaped
        $regex = preg_replace("/\|(?![\$])/", '\|$1', $regex);

        $this->regex = $regex;
    }
}
