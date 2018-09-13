<?php

namespace ThatsUs;

use Illuminate\Support\Facades\App;
use ReflectionClass;
use Closure;

trait BetterBind
{

    public function betterBind(string $signature, Closure $closure, &$caught_params = [])
    {
        $binder = new BetterBinder();
        App::bind($signature, function ($app, $params) use ($signature, $closure, &$caught_params, $binder) {
            $caught_params = $params;
            if (class_exists($signature)) {
                $this->assertParamsMatchConstructor($signature, $params, $binder->getIgnoredParameters());
            }
            return $closure($app, $params);
        });
        return $binder;
    }

    public function betterInstance(string $signature, $instance, &$caught_params = [])
    {
        return $this->betterBind($signature, function () use ($instance) { return $instance; }, $caught_params);
    }

    public function assertParamsMatchConstructor(string $class_name, array $params, array $ignore_params = [])
    {
        $class = new ReflectionClass($class_name);
        $constructor = $class->getConstructor();
        if (!$constructor) {
            $this->assertCount(0, $params, "Parameters provided to class with no constructor: `{$class_name}`");
        } else {
            collect($constructor->getParameters())
                ->each(function ($parameter) use (&$params, $class_name, $ignore_params) {
                    $name = $parameter->getName();
                    if (!$parameter->isOptional() && !in_array($name, $ignore_params)) {
                        $this->assertTrue(isset($params[$name]), "Required parameter `{$name}` not provided to class constructor for `{$class_name}`.");
                    }
                    if ($parameter->getType() && isset($params[$name])) {
                        $real_type = gettype($params[$name]);
                        if ($real_type === 'object') {
                            $real_class = $real_type === 'object' ? get_class($params[$name]) : null;
                            $msg = "Parameter `{$name}` for `{$class_name}` is a `" . $real_class . "`, but a `" . $parameter->getType() . "` is expected.";
                            $this->assertFalse($parameter->getType()->isBuiltIn(), $msg);
                            $this->assertInstanceOf($parameter->getType()->__toString(), $params[$name], $msg);
                        } else {
                            $this->assertEquals($parameter->getType()->__toString(), $real_type, "Parameter `{$name}` for `{$class_name}` is a `" . $real_type . "`, but a `" . $parameter->getType() . "` is expected.");
                        }
                    }
                    unset($params[$name]);
                });
            $extra_params = collect($params)
                ->keys()
                ->map(function ($param) {
                    return "`{$param}`";
                })
                ->implode(', ');
            $this->assertCount(0, $params, "Extra parameters provided to class constructor for `{$class_name}`: {$extra_params}");
        }
    }
}
