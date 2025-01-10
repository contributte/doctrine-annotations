# Contributte Doctrine Annotations

Integration of [Doctrine Annotations](https://www.doctrine-project.org/projects/annotations.html) for Nette Framework.

## Content

- [Installation](#installation)
- [Configuration](#configuration)
  - [Minimal configuration](#minimal-configuration)
  - [Advanced configuration](#advanced-configuration)
  - [Caching](#caching)
- [Usage](#usage)

## Installation

Install package using composer.

```bash
composer require nettrine/annotations
```

Register prepared [compiler extension](https://doc.nette.org/en/dependency-injection/nette-container) in your `config.neon` file.

```yaml
extensions:
  nettrine.annotations: Nettrine\Annotations\DI\AnnotationsExtension
```

> [!NOTE]
> This is just **Annotations**, for **ORM** use [nettrine/orm](https://github.com/contributte/doctrine-orm) or **DBAL** use [nettrine/dbal](https://github.com/contributte/doctrine-dbal).

## Configuration

### Minimal configuration

```neon
nettrine.annotations:
  debug: %debugMode%
```

### Advanced configuration

```yaml
nettrine.annotations:
  debug: <boolean>
  ignore: <string[]>
  cache: <class|service>
```

**Example**

```yaml
nettrine.annotations:
  debug: %debugMode%
  ignore: [author, since, see]
  cache: Doctrine\Common\Cache\PhpFileCache(%tempDir%/cache/doctrine)
```

## Usage

```php
use Doctrine\Common\Annotations\Reader;

class MyReader
{

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

You can create, define and read your own annotations. Take a look [how to do that](https://www.doctrine-project.org/projects/doctrine-annotations/en/2.0/index.html).

### Caching

> [!TIP]
> Take a look at more information in official Doctrine documentation:
> - https://www.doctrine-project.org/projects/annotations.html

A Doctrine Annotations reader can be very slow because it needs to parse all your entities and their annotations.

> [!WARNING]
> Cache adapter must implement `Psr\Cache\CacheItemPoolInterface` interface.
> Use any PSR-6 + PSR-16 compatible cache library like `symfony/cache` or `nette/caching`.

In the simplest case, you can define only `cache`.

```neon
nettrine.annotations:
  # Create cache manually
  cache: App\CacheService(%tempDir%/cache/orm)

  # Use registered cache service
  cache: @cacheService
```

> [!IMPORTANT]
> You should always use cache for production environment. It can significantly improve performance of your application.
> Pick the right cache adapter for your needs.
> For example from symfony/cache:
>
> - `FilesystemAdapter` - if you want to cache data on disk
> - `ArrayAdapter` - if you want to cache data in memory
> - `ApcuAdapter` - if you want to cache data in memory and share it between requests
> - `RedisAdapter` - if you want to cache data in memory and share it between requests and servers
> - `ChainAdapter` - if you want to cache data in multiple storages

## Examples

> [!TIP]
> Take a look at more examples in [contributte/doctrine](https://github.com/contributte/doctrine/tree/master/.docs).
