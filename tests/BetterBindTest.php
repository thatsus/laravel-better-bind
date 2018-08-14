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
        $this->appBind('x', function () use ($object) { return $object; }, $params);
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
        $this->appBind('x', function () use ($object) { return $object; }, $params);
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
        $this->appBind(INeedNoParams::class, function () use ($object) { return $object; }, $params);
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
        $this->appBind(INeedNoParams::class, function () use ($object) { return $object; }, $params);
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
        $this->appBind(INeedParams::class, function () use ($object) { return $object; }, $params);
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
        $this->appBind(INeedParams::class, function () use ($object) { return $object; }, $params);
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
        $this->appBind(INeedParams::class, function () use ($object) { return $object; }, $params);
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
     * Too many params are passed in.
     */
    public function testBindHeadfulClassWithTooManyParams()
    {
        $object = new stdClass();
        $this->appBind(INeedParams::class, function () use ($object) { return $object; }, $params);
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
        $this->appInstance('x', $object, $params);
        $got = App::makeWith('x', ['y' => 'z']);
        $this->assertEquals($object, $got);
        $this->assertEquals($params['y'], 'z');
        $this->assertCount(1, $params);
    }
}
