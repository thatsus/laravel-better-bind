# laravel-better-bind
A better bind feature for automated tests in Laravel/Lumen 5+.

# Why BetterBind is better

1. It's less verbose than Laravel's built-in option.
2. It protects you against missing constructor parameters.
3. You can choose to check the constructor parameters for correctness.

Automated testing in Laravel using mocks means injecting objects using the 
Application's `bind` method.

This can have some drawbacks.

 * `App::bind` is verbose in Laravel.
 * `App::bind` doesn't test that an object is instantiated with the right parameters.

BetterBind provides a syntactically friendly mechanism to verify that 
constructor parameters match your target class.

Missing parameters cause an assertion failure.

Extra parameters cause an assertion failure.

It can be a one-liner.

BetterBind also provides a way to capture the constructor parameters so you 
can run your own assertions on them.

# Installation

```
composer require thatsus/laravel-better-bind
```

```php
class TestCase
{
    use \ThatsUs\BetterBind;

    ...
}
```

# Example Test

In this example we expect the `Dog::bark` method to create a `Sound` object with 
itself as the value for the constructor's `$animal` parameter.

First, we use the `betterInstance` method from BetterBind to provide the mock to 
the code. Then we capture the `$params` argument and check at the end that it 
has the parameter values we expect.

```php
class DogTest extends TestCase
{
    public function testBark()
    {
        $mock = Mockery::mock(Sound::class);
        $mock->shouldReceive('emit')
            ->once();
        $this->betterInstance(Sound::class, $mock, $params);

        $dog = new Dog();
        $dog->bark();

        $this->assertEquals(['animal' => $dog], $params);
    }
}
```

```php
class Sound
{
    public function __construct(Animal $animal)
    {
        // ...
    }
}
```

## Passing Code

Gallant wrote this code correctly with the right parameters for the constructor.

```php
class Dog
{
    public function bark()
    {
        App::makeWith(Sound::class, ['animal' => $this])->emit();
    }
}
```

## Failing Code 1

Goofus forgot to include the name of the parameter for the constructor.

```php
class Dog
{
    public function bark()
    {
        App::makeWith(Sound::class, [$this])->emit();
    }
}
```

```
1) DogTest::testBark
Required parameter `animal` not provided to class constructor for `Sound`
```

## Failing Code 2

Now Goofus included an extra parameter for the constructor by accident.

```php
class Dog
{
    public function bark()
    {
        App::makeWith(Sound::class, ['animal' => $this, 'volume' => 'loud'])->emit();
    }
}
```

```
1) DogTest::testBark
Extra parameters provided to class constructor for `Sound`: `volume`
```

# Methods

### betterInstance($signature, $object, [&$params = []])

 * $signature - string, the class name or other string requested in a 
                `makeWith` call.
 * $object    - mixed, the value to return from the `makeWith` call.
 * $params    - optional, when `makeWith` is called, this variable will be 
                updated with the parameters sent to `makeWith` so that your 
                test code may make assertions against them.

If `$signature` is a string that does not refer to an existing class, no 
assertions will run against the parameters.

### betterBind($signature, $closure, [&$params = []])

 * $signature - string, the class name or other string requested in a 
                `makeWith` call.
 * $closure   - a closure that will be called when `makeWith` is called. The 
                return value will be returned from `makeWith`. The parameters
                will be `$app`, the Application object, and `$params`, the 
                array of parameters sent to `makeWith`.
 * $params    - optional, when `makeWith` is called, this variable will be 
                updated with the parameters sent to `makeWith` so that your 
                test code may make assertions against them.

If `$signature` is a string that does not refer to an existing class, no 
assertions will run against the parameters.

### betterBind(...)->ignoreParameters($param1, $param2, ...)

 Do not check that the given parameters name are given by the call to 
 `makeWith`. Allow Laravel to supply them.

 * $paramN - string, the name of a parameter

# I'm not convinced. Can't I do this without BetterBind?

You can do some of the same stuff without this library.

Compare the following versions of the test.

```php
class DogTest extends TestCase
{
    public function testBark()
    {
        $mock = Mockery::mock(Sound::class);
        $mock->shouldReceive('emit')
            ->once();
        $this->betterInstance(Sound::class, $mock, $params);

        $dog = new Dog();
        $dog->bark();

        $this->assertEquals(['animal' => $dog], $params);
    }
}
```

```php
class DogTest extends TestCase
{
    public function testBark()
    {
        $mock = Mockery::mock(Sound::class);
        $mock->shouldReceive('emit')
            ->once();
        App::bind(Sound::class, function ($app, $bind_params) use (&$params, $mock) {
            $params = $bind_params;
            return $mock;
        });

        $dog = new Dog();
        $dog->bark();

        $this->assertEquals(['animal' => $dog], $params);
    }
}
```

The obvious drawback to the version that doesn't use BetterBind is that there 
are extra lines, and one of them is very verbose. The secret extra drawback 
here is that nothing tests to ensure that the requirements to the real `Sound` 
class's constructor are met.

What will happen if you write the code from "Failing Code 1"?

```php
class Dog
{
    public function bark()
    {
        App::makeWith(Sound::class, [$this])->emit();
    }
}
```

Laravel will detect that `Sound`'s constructor typehints an `Animal` object. 
But no 'animal' element is in the params, so Laravel will new up an `Animal` 
object to do the job. There will be no test failure.

Using BetterBind, the missing value will be detected and the test will fail.

If you _want_ Laravel to fill in a new `Animal` object itself, you can add 
`->ignoreParameters('animal')`.

# Compatibility

We believe this code is compatible with all versions of Laravel 5+ and Lumen 
5+, but testing it is a chore because `make` changed to `makeWith` in 5.4 just 
to confuse everybody. Drop us a line and let us know how great it is and what 
version of Laravel you're working with.

# Contribution

If you find a bug or want to contribute to the code or documentation, you can help by submitting an [issue](https://github.com/thatsus/laravel-better-bind/issues) or a [pull request](https://github.com/thatsus/laravel-better-bind/pulls).

# License

[MIT](http://opensource.org/licenses/MIT)


