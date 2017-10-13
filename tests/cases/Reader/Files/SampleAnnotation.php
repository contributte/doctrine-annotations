<?php

namespace Tests\Reader\Files;

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
