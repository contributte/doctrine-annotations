# Nettrine Annotations

[Doctrine\Annotations](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html) for Nette Framework.

## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Example - create own annotation](#example)

## Setup

Install package

```bash
composer require nettrine/annotations
```

Register extension

```yaml
extensions:
    annotations: Nettrine\Annotations\DI\AnnotationsExtension
```

## Configuration

```yaml
annotations:
    debug: %debugMode%
    ignore: []
    cache: Doctrine\Common\Cache\FilesystemCache
```

Optionally you can configure ignored annotations:

```yaml
annotations:
    ignore:
        - someIgnoredAnnotation
```

Refer already defined cache (instance of `Doctrine\Common\Cache`).

```yaml
annotations:
    cache: @mycache
```

## Example

Create own annotation. Specify your targets `CLASS, METHOD, PROPERTY, ALL, ANNOTATION`.

[More you can find at official documentation.](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html#annotation-classes)

```php
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class SampleAnnotation
{

	/** @var string|NULL */
	private $value;

	/**
	 * @param string[] $values
	 */
	public function __construct(array $values)
	{
		$this->value = isset($values['value']) ? $values['value'] : NULL;
	}

	/**
	 * @return string|NULL
	 */
	public function getValue()
	{
		return $this->value;
	}

}
```

Use annotation in your class.

```php
/**
 * @SampleAnnotation(value="foo")
 */
class SampleClass
{

}
```

Now you can use `Reader` service from your Container. 

```php
/** @var Doctrine\Common\Annotations\Reader @inject */
public $reader;
```

And get class, method or property annotations.

```php
$annotations = $this->reader->getClassAnnotations(new \ReflectionClass(SampleClass::class));
$annotations[0]->getValue(); //foo
```
