<?php

declare(strict_types=1);

namespace Pierresh\Simca\Model;

class Objective
{
	public function __construct(
		public float $value,
		public string $color,
		public float $width
	) {
	}
}
