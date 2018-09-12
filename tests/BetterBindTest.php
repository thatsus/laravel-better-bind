<?php

class BetterBindTest extends TestCase
{

    use \ThatsUs\BetterBind;

    /**
     * A non-class-name is used for the signature, so we don't expect any 
     * trouble from constructor params.
     * No params are passed in.
     */
    public function testBindNonsenseWordNoParams()
    {
        $object = new stdClass();
        $this->betterBind('x', function () use ($object) { return $object; }, $params);
        $got = App::makeWith('x', []);
        $this->assertEquals($object, $got);
        $this->assertCount(0, $params);
    }

    /**
     * A non-class-name is used for the signature, so we don't expect any 
     * trouble from constructor params.
     * Params are passed in and received.
     */
    public function testBindNonsenseWordAndParams()
    {
        $object = new stdClass();
        $this->betterBind('x', function () use ($object) { return $object; }, $params);
        $got = App::makeWith('x', ['y' => 'z']);
        $this->assertEquals($object, $got);
        $this->assertEquals($params['y'], 'z');
        $this->assertCount(1, $params);
    }

    /**
     * A class that needs no params.
     * No params are passed in.
     */
    public function testBindHeadlessClassNoParams()
    {
        $object = new stdClass();
        $this->betterBind(INeedNoParams::class, function () use ($object) { return $object; }, $params);
        $got = App::makeWith(INeedNoParams::class, []);
        $this->assertEquals($object, $got);
        $this->assertCount(0, $params);
    }

    /**
     * A class that needs no params.
     * Params are passed in. (Failure!)
     * Params are caught even though there is failure.
     */
    public function testBindHeadlessClassWithParams()
    {
        $object = new stdClass();
        $this->betterBind(INeedNoParams::class, function () use ($object) { return $object; }, $params);
        $e = null;
        try {
            App::makeWith(INeedNoParams::class, ['y' => 'z']);
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
        }
        $this->assertNotNull($e);
        $this->assertRegExp('/INeedNoParams/', $e->getMessage());
        $this->assertRegExp('/no constructor/', $e->getMessage());
        $this->assertEquals('z', $params['y']);
        $this->assertCount(1, $params);
    }

    /**
     * A class that needs some params.
     * All params are passed in.
     */
    public function testBindHeadfulClassWithAllParams()
    {
        $object = new stdClass();
        $this->betterBind(INeedParams::class, function () use ($object) { return $object; }, $params);
        $got = App::makeWith(INeedParams::class, ['first_param' => 123, 'second_param' => 456]);
        $this->assertEquals($object, $got);
        $this->assertEquals(123, $params['first_param']);
        $this->assertEquals(456, $params['second_param']);
        $this->assertCount(2, $params);
    }

    /**
     * A class that needs some params.
     * Only one param is passed in, the other can be default.
     */
    public function testBindHeadfulClassWithOneParam()
    {
        $object = new stdClass();
        $this->betterBind(INeedParams::class, function () use ($object) { return $object; }, $params);
        $got = App::makeWith(INeedParams::class, ['first_param' => 123]);
        $this->assertEquals($object, $got);
        $this->assertEquals(123, $params['first_param']);
        $this->assertCount(1, $params);
    }

    /**
     * A class that needs some params.
     * No params are passed in (FAILURE!)
     */
    public function testBindHeadfulClassNoParams()
    {
        $object = new stdClass();
        $this->betterBind(INeedParams::class, function () use ($object) { return $object; }, $params);
        $e = null;
        try {
            App::makeWith(INeedParams::class, []);   
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
        }
        $this->assertNotNull($e);
        $this->assertRegExp('/INeedParams/', $e->getMessage());
        $this->assertRegExp('/Required parameter/', $e->getMessage());
        $this->assertCount(0, $params);
    }

    /**
     * A class that needs some params.
     * Too many params are passed in. (FAILURE!)
     */
    public function testBindHeadfulClassWithTooManyParams()
    {
        $object = new stdClass();
        $this->betterBind(INeedParams::class, function () use ($object) { return $object; }, $params);
        $e = null;
        try {
            App::makeWith(INeedParams::class, ['first_param' => 123, 'second_param' => 456, 'failure_param' => 789]);   
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
        }
        $this->assertNotNull($e);
        $this->assertRegExp('/INeedParams/', $e->getMessage());
        $this->assertRegExp('/failure_param/', $e->getMessage());
        $this->assertEquals(123, $params['first_param']);
        $this->assertEquals(456, $params['second_param']);
        $this->assertEquals(789, $params['failure_param']);
        $this->assertCount(3, $params);
    }

    public function testInstance()
    {
        $object = new stdClass();
        $this->betterInstance('x', $object, $params);
        $got = App::makeWith('x', ['y' => 'z']);
        $this->assertEquals($object, $got);
        $this->assertEquals($params['y'], 'z');
        $this->assertCount(1, $params);
    }

    public function testIgnoreParameters()
    {
        $object = new stdClass();
        $this->betterInstance(INeedParams::class, $object, $params)
            ->ignoreParameters('first_param');
        $got = App::makeWith(INeedParams::class, ['second_param' => 456]);
        $this->assertSame($object, $got);
    }

    public function testTypehintsSuccess()
    {
        $object = new stdClass();
        $this->betterInstance(INeedParamsWithTypes::class, $object, $params);
        $got = App::makeWith(INeedParamsWithTypes::class, [
            'first_param' => Mockery::mock(stdClass::class),
            'second_param' => 'some string',
        ]);
        $this->assertEquals($object, $got);
        $this->assertInstanceOf(stdClass::class, $params['first_param']);
        $this->assertEquals('some string', $params['second_param']);
    }

    public function testTypehintsFail()
    {
        $object = new stdClass();
        $this->betterInstance(INeedParamsWithTypes::class, $object, $params);
        $e = null;
        try {
            App::makeWith(INeedParamsWithTypes::class, [
                'first_param' => 'oh no',
                'second_param' => 'some string',
            ]);
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
        }
        $this->assertNotNull($e);
        $this->assertRegExp('/INeedParamsWithTypes/', $e->getMessage());
        $this->assertRegExp('/first_param/', $e->getMessage());
        $this->assertRegExp('/stdClass/', $e->getMessage());
        $this->assertRegExp('/string/', $e->getMessage());
        $this->assertNotRegExp('/second_param/', $e->getMessage());
        $this->assertEquals('oh no', $params['first_param']);
        $this->assertEquals('some string', $params['second_param']);
    }
}
