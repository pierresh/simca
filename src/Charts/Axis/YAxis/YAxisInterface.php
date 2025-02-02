<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Axis\YAxis;

interface YAxisInterface
{
	public function getMinY(): float;

	public function getMaxY(): float;

	public function getUnit(): string;

	public function setUnit(string $unit): void;

	/** @return float[] */
	public function autoGridLines(int $nlines): array;

	/** @return float[] */
	public static function computeGridLines(
		float $ymin,
		float $ymax,
		int $nlines
	): array;

	public function maxGridValue(int $nlines): float;

	public function convertYValue(int|float $y): float;
}
