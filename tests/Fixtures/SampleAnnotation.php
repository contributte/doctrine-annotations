<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class SampleAnnotation
{

	private string|null $value;

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
