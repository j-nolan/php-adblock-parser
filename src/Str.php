<?php
namespace Limonte;

class Str
{
    public static function startsWith($haystack, $needle)
    {
        $length = mb_strlen($needle);
        return (mb_substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle)
    {
        $length = mb_strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (mb_substr($haystack, -$length) === $needle);
    }

    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }
}
