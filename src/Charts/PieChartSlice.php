<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use Pierresh\Simca\Model\Dot;

class PieChartSlice
{
	private readonly float $cx;
	private readonly float $cy;

	public function __construct(
		Dot $c,
		private readonly float $radius,
		private readonly float $startAngle,
		private readonly float $endAngle
	) {
		$this->cx = (float) $c->x;
		$this->cy = (float) $c->y;
	}

	public function getPath(): string
	{
		$start = $this->polarToCartesian($this->radius, $this->endAngle);

		$end = $this->polarToCartesian($this->radius, $this->startAngle);

		$largeArcFlag = $this->endAngle - $this->startAngle <= 180 ? '0' : '1';

		// prettier-ignore
		$d = [
			'M', $this->cx, $this->cy,
			'L', $start['x'], $start['y'],
			'A', $this->radius, $this->radius,
			0, $largeArcFlag, 0,
			$end['x'], $end['y'], 'Z',
		];

		return implode(' ', $d);
	}

	/** @return array{x:float, y:float} */
	private function polarToCartesian(
		float $radius,
		float $angleInDegrees
	): array {
		$angleInRadians = deg2rad($angleInDegrees);

		return [
			'x' => $this->cx + $radius * cos($angleInRadians),
			'y' => $this->cy + $radius * sin($angleInRadians),
		];
	}
}
