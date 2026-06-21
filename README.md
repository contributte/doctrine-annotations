![](https://heatbadger.now.sh/github/readme/contributte/doctrine-annotations/)

<p align=center>
  <a href="https://github.com/contributte/doctrine-annotations/actions"><img src="https://badgen.net/github/checks/nettrine/annotations/master?annotations=300"></a>
  <a href="https://codecov.io/gh/contributte/doctrine-annotations"><img src="https://badgen.net/codecov/c/github/contributte/doctrine-annotations?cache=300"></a>
  <a href="https://packagist.org/packages/nettrine/annotations"><img src="https://badgen.net/packagist/dm/nettrine/annotations"></a>
  <a href="https://packagist.org/packages/nettrine/annotations"><img src="https://badgen.net/packagist/v/nettrine/annotations"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/nettrine/annotations"><img src="https://badgen.net/packagist/php/nettrine/annotations"></a>
  <a href="https://github.com/contributte/doctrine-annotations"><img src="https://badgen.net/github/license/contributte/doctrine-annotations"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Integration of [Doctrine Annotations](https://www.doctrine-project.org/projects/annotations.html) for Nette Framework.

## Versions

| State  | Version | Branch   | Nette  | PHP     |
|--------|---------|----------|--------|---------|
| dev    | `^0.10` | `master` | `3.2+` | `>=8.2` |
| stable | `^0.9`  | `master` | `3.2+` | `>=8.2` |

## Installation

To install the latest version of `nettrine/annotations` use [Composer](https://getcomposer.org).

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

### Minimal Configuration

```neon
nettrine.annotations:
  debug: %debugMode%
```

### Advanced Configuration

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

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintaining by these authors.

<a href="https://github.com/f3l1x">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
