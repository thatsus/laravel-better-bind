<?php

namespace ThatsUs;

class BetterBinderTest extends \TestCase
{

    public function testExists()
    {
        new BetterBinder();
    }

    public function testIgnoreParameters_Array()
    {
        $binder = new BetterBinder();
        $results = $binder->ignoreParameters(['x']);

        $this->assertSame($binder, $results);
        $this->assertEquals(['x'], $binder->getIgnoredParameters());
    }

    public function testIgnoreParameters_String()
    {
        $binder = new BetterBinder();
        $results = $binder->ignoreParameters('x');

        $this->assertSame($binder, $results);
        $this->assertEquals(['x'], $binder->getIgnoredParameters());
    }
}
