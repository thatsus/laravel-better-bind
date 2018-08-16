<?php

namespace ThatsUs;

class BetterBinderTest extends \TestCase
{

    public function testExists()
    {
        new BetterBinder();
    }

    public function testIgnoreParameters()
    {
        $binder = new BetterBinder();
        $results = $binder->ignoreParameters(['x']);

        $this->assertSame($binder, $results);
        $this->assertEquals(['x'], $binder->getIgnoreParameters());
    }
}
