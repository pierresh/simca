<?php

declare(strict_types=1);

namespace Pierresh\Simca\Model;

class Dot
{
	public function __construct(
		public ?float $x = null,
		public ?float $y = null,
		public ?float $value = null
	) {}
}
