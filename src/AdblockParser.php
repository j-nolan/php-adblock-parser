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
     * @param  string|array  $path
     */
    public function loadRules($path)
    {
        // single resource
        if (is_string($path)) {
            $content = @file_get_contents($path);
            if ($content) {
                $rules = preg_split("/(\r\n|\n|\r)/", $content);
                $this->addRules($rules);
            }
        // array of resources
        } elseif (is_array($path)) {
            foreach ($path as $item) {
                $this->loadRules($item);
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
        foreach ($this->rules as $rule) {
            if ($rule->isComment() || $rule->isHtml()) {
                continue;
            }

            if ($rule->matchUrl($url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string  $url
     *
     * @return boolean
     */
    public function shouldNotBlock($url)
    {
        return !$this->shouldBlock($url);
    }
}
