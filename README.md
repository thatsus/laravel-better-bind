# laravel-better-bind
A better bind feature for automated tests in Laravel

# Why BetterBind is better

Automated testing in Laravel using mocks means injecting objects into code
using the Application object's `bind` and `makeWith` methods.

This can have some drawbacks.

 * It's verbose.
 * Failures in the way objects are instantiated aren't caught.

BetterBind provides an easy-to-use mechanism to verify that the parameters your
operation code provides to your mocked class are the parameters that the 
underlying class expects.

Missing parameters cause an assertion failure.

Extra parameters cause an assertion failure.

It can be a one-liner.

BetterBind also offers a way to capture the constructor parameters so you can 
run your own assertions on them.

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

In this example we expect the `bark` method to create a `Sound` object with 
the value 'bark' for the constructor's `$sound` parameter. We use the 
`appInstance` method from BetterBind to provide the mock to the code.

```php
class DogTest extends TestCase
{
    public function testBark()
    {
        $mock = Mockery::mock(Sound::class);
        $mock->shouldReceive('emit')
            ->with('bark')
            ->once();
        $this->appInstance(Sound::class, $mock, $params);

        $dog = new Dog();
        $dog->bark();

        $this->assertEquals(['sound' => 'bark'], $params);
    }
}
```

## Passing Code

Galant wrote this code correctly with the right parameters for the constructor.

```php
class Dog
{
    public function bark()
    {
        App::makeWith(Sound::class, ['sound' => 'bark'])->emit();
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
        App::makeWith(Sound::class, ['bark'])->emit();
    }
}
```

```
1) DogTest::testBark
Required parameter `sound` not provided to class constructor for `Sound`
```

## Failing Code 2

Now Goofus included an extra parameter for the constructor by accident.

```php
class Dog
{
    public function bark()
    {
        App::makeWith(Sound::class, ['sound' => 'bark', 'volume' => 'loud'])->emit();
    }
}
```

```
1) DogTest::testBark
Extra parameters provided to class constructor for `Sound`: `volume`
```

# Methods

### appInstance($signature, $object, [&$params = []])

 * $signature - string, the class name or other string requested in a 
                `makeWith` call.
 * $object    - mixed, the value to return from the `makeWith` call.
 * $params    - optional, when `makeWith` is called, this value will be updated
                with the parameters sent to `makeWith` so that test code may make
                assertions against them.

If `$signature` is a string that does not refer to an existing class, no 
assertions will run against the parameters.

### appBind($signature, $closure, [&$params = []])

 * $signature - string, the class name or other string requested in a 
                `makeWith` call.
 * $closure   - a closure that will be called when `makeWith` is called. The 
                return value will be returned from `makeWith`. The parameters
                will be `$app`, the Application object, and `$params`, the 
                array of parameters sent to `makeWith`.
 * $params    - optional, when `makeWith` is called, this value will be updated
                with the parameters sent to `makeWith` so that test code may make
                assertions against them.

If `$signature` is a string that does not refer to an existing class, no 
assertions will run against the parameters.

# Compatiblity

We believe this code is compatible with all versions of Laravel 5+ and Lumen 
5+, but testing is a chore because `make` changed to `makeWith` in 5.4 just 
to confuse everybody. Drop us a line and let us know how great it is and what 
version of Laravel you're working with.

# Contribution

If you find a bug or want to contribute to the code or documentation, you can help by submitting an [issue](https://github.com/thatsus/laravel-better-bind/issues) or a [pull request](https://github.com/thatsus/laravel-better-bind/pulls).

# License

[MIT](http://opensource.org/licenses/MIT)


