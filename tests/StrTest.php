<?php
namespace Limonte\Str;

use Limonte\Str;

class StrTest extends \PHPUnit_Framework_TestCase
{
    public function testStartsWith()
    {
        $this->assertTrue(Str::startsWith('/abc', '/'));
        $this->assertTrue(Str::startsWith('/abc', '/a'));
        $this->assertFalse(Str::startsWith('/abc', 'ab'));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith('/abc', ''));
        $this->assertTrue(Str::endsWith('/abc', 'c'));
        $this->assertTrue(Str::endsWith('/abc', 'bc'));
        $this->assertFalse(Str::endsWith('/abc', 'ab'));
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('/abc', '/a'));
        $this->assertTrue(Str::contains('/abc', 'ab'));
        $this->assertFalse(Str::contains('/abc', 'acb'));
    }
}
