# Contributing!

Hi! Thanks for contributing!

For the best experience for everyone, there's a few guidelines we'd like everyone to follow.

## Code Style

This is a work-in-progress, so feel free to put ideas forward as to coding styles. The current guideline is based on suggestions from team members, and the recommendations from the [PHP Framework Interop Group](http://www.php-fig.org/)

* Files are UTF-8 encoded without a BOM ([PSR-1][1])
* Only use long-syntax php tags: `<?php` and omit the closing tag at the end of a file. ([PSR-1][1])
* PHP 5.5 please, nothing newer as it won't run in production.
* Files contain a single class definition, and the file is named for the class. 
  Alternatively, they should contain a script, not both. ([PSR-1][1])

### Future work

Namespacing currently isn't implemented, but we should be looking towards this goal.

### Indentation and Braces

* A single tab per level of indentation please.

* Opening braces go on the next line for methods and classes, but on the same line for control structures ([PSR-1][1]).

* Spaces before and after brackets, not inside ([PSR-1][1]).

Quick example:

```php
class Foo extends Bar implements FooInterface
{
	public function sampleFunction($a, $b = null)
	{
		if ($a === $b) {
			bar();
		} elseif ($a > $b) {
			$foo->bar($arg1);
		} else {
			BazClass::bar($arg2, $arg3);
		}
	}

	final public static function bar()
	{
		// method body
	}
}
```

### Line length and wrapping

Soft limit of 80 chars, hard limit of 120 please. Lines longer than 130 chars make code review on GitHub nasty.

### Naming

* `UpperCamelCase` for classes. ([PSR-1][1])
* `lowerCamelCase` for methods. ([PSR-1][1])
* `UPPER_CASE` for constants. ([PSR-1][1])
* Properties should have names which closely match their backing fields:

```php
<?php
class Foo 
{
	private $foo;

	public function getFoo() 
	{
		return $this->foo;
	}

	public function setFoo($foo) 
	{
		$this->foo = $foo;
	}
}
```

### Misc

* The ternary (`?:`) operator should only be used where appropriate - short expressions only please!
* Heredoc/Nowdoc should not be used for output - use templates. Extended SQL statements are OK.

## How-tos

### Database

### Templating

[1]: http://www.php-fig.org/psr/psr-1/

