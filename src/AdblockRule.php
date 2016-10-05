<?php
namespace Limonte;

class AdblockRule
{
    private $rule;

    private $regex;

    public function __construct($rule)
    {
        $this->rule = $rule;
        $this->makeRegex();
    }

    /**
     * @param  string  $url
     *
     * @return  boolean
     */
    public function matchUrl($url)
    {
        return (boolean)preg_match(
            '/' . $this->getRegex() . '/',
            $url
        );
    }

    /**
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    private function makeRegex()
    {
        if (empty($this->rule)) {
            throw new InvalidRuleException("Empty rule");
        }

        $regex = $this->rule;

        // Check if the rule isn't already regexp
        if ($this->startsWith($regex, '/') && $this->endsWith($regex, '/')) {
            $this->regex = mb_substr($this->rule, 1, mb_strlen($this->rule) - 2);

            if (empty($this->regex)) {
                throw new InvalidRuleException("Empty rule");
            }

            return;
        }

        // escape special regex characters
        $regex = preg_replace("/([\\\.\$\+\?\{\}\(\)\[\]\/])/", "\\\\$1", $this->rule);

        // Separator character ^ matches anything but a letter, a digit, or
        // one of the following: _ - . %. The end of the address is also
        // accepted as separator.
        $regex = str_replace("^", "([^\w\d_\-.%]|$)", $regex);

        // * symbol
        $regex = str_replace("*", ".*", $regex);

        // | in the end means the end of the address
        if ($this->endsWith($regex, '|')) {
            $regex = mb_substr($regex, 0, mb_strlen($regex) - 1) . '$';
        }

        // || in the beginning means beginning of the domain name
        if ($this->startsWith($regex, '||')) {
            if (mb_strlen($regex) > 2) {
                // http://tools.ietf.org/html/rfc3986#appendix-B
                $regex = "^([^:\/?#]+:)?(\/\/([^\/?#]*\.)?)?" . mb_substr($regex, 2);
            }
        // | in the beginning means start of the address
        } elseif ($this->startsWith($regex, '|')) {
            $regex = '^' . mb_substr($regex, 1);
        }

        // other | symbols should be escaped
        $regex = preg_replace("/\|(?![\$])/", "\|$1", $regex);

        $this->regex = $regex;
    }

    private function startsWith($haystack, $needle)
    {
        $length = mb_strlen($needle);
        return (mb_substr($haystack, 0, $length) === $needle);
    }

    private function endsWith($haystack, $needle)
    {
        $length = mb_strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (mb_substr($haystack, -$length) === $needle);
    }
}
