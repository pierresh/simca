<?php declare(strict_types=1);

namespace Pierresh\Simca\Charts\Axis\XAxis;

class LabelPosition
{
	public function __construct(
		public readonly int|float $index,
		public readonly string $label
	) {}
}
