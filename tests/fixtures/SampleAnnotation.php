<?php declare(strict_types = 1);

namespace Tests\Fixtures;

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
		$this->value = $values['value'] ?? null;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

}
