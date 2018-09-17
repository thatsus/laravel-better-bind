<?php

namespace ThatsUs;

use Illuminate\Support\Facades\App;
use ReflectionClass;
use ReflectionParameter;
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
                        $real_class = $real_type == 'object' ? get_class($params[$name]) : null;
                        $msg = "Constructor parameter `{$name}` for `{$class_name}` is a `" . ($real_class ?: $real_type) . "`, but a `" . $parameter->getType() . "` is expected.";
                        $this->assertParameterTypeMatchesValue($parameter, $class_name, $params[$name], $msg);
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

    public function assertParameterTypeMatchesValue(ReflectionParameter $parameter, string $self_class, $value, string $msg)
    {
        if (!$parameter->getType()) {
            return;
        }
        $type_name = $parameter->getType()->__toString();
        if ($parameter->getType()->isBuiltIn()) {
            $this->assertInternalTypeCast($type_name, $value, $msg);
        } elseif ($type_name == 'self') {
            $this->assertInstanceOf($self_class, $value, $msg);
        } else {
            $this->assertInstanceOf($type_name, $value, $msg);
        }
    }

    public function assertInternalTypeCast($type_name, $value, $msg)
    {
        // Each of the following `if` clauses allows for type coercion
        if (is_numeric($value) && in_array($type_name, ['bool', 'float', 'int', 'string'])) {
            return;
        }
        if (is_string($value) && $type_name == 'bool') {
            return;
        }
        if (is_bool($value) && in_array($type_name, ['int', 'float', 'string'])) {
            return;
        }
        if (is_object($value) && $type_name == 'string' && method_exists($value, '__toString')) {
            return;
        }
        $this->assertInternalType($type_name, $value, $msg);
    }
}
