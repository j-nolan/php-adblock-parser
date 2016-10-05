<?php
namespace Limonte;

class AdblockRule
{
    private $rule;

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return  string
     */
    public function toRegex()
    {
        // Check if the rule isn't already regexp
        if ($this->startswith('/') && $this->endswith('/')) {
            $rule = mb_substr($this->rule, 1, mb_strlen($this->rule) - 2);

            if (empty($rule)) {
                throw new InvalidRuleException("Invalid rule " . $this->rule);
            }

            return $rule;
        }
    }

    private function startsWith($needle)
    {
        $length = mb_strlen($needle);
        return (mb_substr($this->rule, 0, $length) === $needle);
    }

    private function endsWith($needle)
    {
        $length = mb_strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (mb_substr($this->rule, -$length) === $needle);
    }
}
