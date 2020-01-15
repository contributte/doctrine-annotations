# Nettrine Annotations

[Doctrine/Annotations](https://www.doctrine-project.org/projects/annotations.html) for Nette Framework.


## Content

- [Setup](#setup)
- [Relying](#relying)
- [Configuration](#configuration)
- [Usage](#usage)
- [Examples](#examples)


## Setup

Install package

```bash
composer require nettrine/annotations
```

Register extension

```yaml
extensions:
  nettrine.annotations: Nettrine\Annotations\DI\AnnotationsExtension
```


## Relying

Take advantage of empowering this package with 1 extra package:

- `doctrine/cache`


### `doctrine/cache`

This package can be enhanced with `doctrine/cache`, use prepared [nettrine/cache](https://github.com/nettrine/cache) integration.

```bash
composer require nettrine/cache
```

```yaml
extensions:
  nettrine.cache: Nettrine\Cache\DI\CacheExtension
```


## Configuration

**Schema definition**

```yaml
nettrine.annotations:
  debug: <boolean>
  ignore: <string[]>
  cache: <class|service>
```

**Under the hood**

```yaml
nettrine.annotations:
  debug: %debugMode%
  ignore: [author, since, see]
  cache: Doctrine\Common\Cache\PhpFileCache(%tempDir%/cache/doctrine)
```

You may omit `cache` key using [nettrine/cache](https://github.com/nettrine/cache) to setup cache.

```yaml
nettrine.annotations:
  debug: %debugMode%
```


## Usage

You can count on [Nette Dependency Injection](https://doc.nette.org/en/3.0/dependency-injection).

```php
use Doctrine\Common\Annotations\Reader;

class MyReader {

  /** @var Reader */
  private $reader;

  public function __construct(Reader $reader)
  {
    $this->reader = $reader;
  }

  public function reader()
  {
    $annotations = $this->reader->getClassAnnotations(new \ReflectionClass(UserEntity::class));
  }

}
```

Register reader `MyReader` under services in NEON file.

```yaml
services:
  - MyReader
```

You can create, define and read your own annotations. Take a look [how to do that](https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/index.html#create-an-annotation-class).


## Examples

You can find more examples in [planette playground](https://github.com/planette/playground) repository.
