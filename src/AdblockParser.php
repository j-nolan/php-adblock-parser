<?php
namespace Limonte;

class AdblockParser
{
    private $rules;

    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * @param  string[]  $rules
     */
    public function addRules($rules)
    {
        $this->rules = array_merge($this->rules, $rules);
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
