<?php

declare(strict_types=1);

namespace App\AdblockParser;

use Pdp\Rules;

class DomainParser
{
    public static function parseRegistrableDomain(string $host): string
    {
        $publicSuffixRulesPath = realpath(__DIR__ . '/../../resources/publicSuffixRules');
        if ($publicSuffixRulesPath && file_exists($publicSuffixRulesPath)) {
            $serializedRules = file_get_contents($publicSuffixRulesPath);
            $publicSuffixRules = unserialize($serializedRules);
            assert($publicSuffixRules instanceof Rules);
        } else {
            $publicSuffixRules = Rules::fromPath(realpath(__DIR__ . '/../../resources/public_suffix_list.dat'));
            $serializedRules = serialize($publicSuffixRules);
            file_put_contents('publicSuffixRules', $serializedRules);
        }
        $result = $publicSuffixRules->resolve($host);

        return $result->registrableDomain()->toString();
    }
}
