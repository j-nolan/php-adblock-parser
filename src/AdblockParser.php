<?php
namespace Limonte;

class AdblockParser
{
    private $rules;

    public function __construct($rules = [])
    {
        $this->rules = [];
        $this->addRules($rules);
    }

    /**
     * @param  string[]  $rules
     */
    public function addRules($rules)
    {
        foreach ($rules as $rule) {
            try {
                $this->rules[] = new AdblockRule($rule);
            } catch (InvalidRuleException $e) {
            }
        }
    }

    /**
     * @return  []
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param  string  $url
     *
     * @return boolean
     */
    public function shouldBlock($url)
    {
        return false;
    }
}
