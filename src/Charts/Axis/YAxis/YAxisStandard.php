<?php declare(strict_types=1);

namespace Pierresh\Simca\Charts\Axis\YAxis;

use Pierresh\Simca\Model\Objective;

/**
 * @phpstan-type Serie array<int, float>
 */
class YAxisStandard implements YAxisInterface
{
	private float $minY;

	private float $maxY;

	private string $unit = '';

	/**
	 * @param array<string> $labels
	 * @param array<Serie> $series
	 * @param array<Objective> $objectives
	 */
	public function __construct(
		private readonly array $labels,
		private readonly array $series,
		private readonly array $objectives,
		private readonly bool $stacked
	) {
		$this->computeMinMax();
	}

	public function getMinY(): float
	{
		return $this->minY;
	}

	public function getMaxY(): float
	{
		return $this->maxY;
	}

	public function getUnit(): string
	{
		return $this->unit;
	}

	public function setUnit(string $unit): void
	{
		$this->unit = $unit;
	}

	private function computeMinMax(): void
	{
		$this->minY = 0;
		$this->maxY = 0.1;

		if ($this->stacked) {
			foreach ($this->labels as $indexLabel => $label) {
				$tmpCumulY = 0;

				foreach ($this->series as $serie) {
					$tmpCumulY += $serie[$indexLabel];
				}

				$this->maxY = max($this->maxY, $tmpCumulY);
			}
		} else {
			foreach ($this->series as $serie) {
				if ($serie === []) {
					continue;
				}

				$this->minY = min($this->minY, min($serie));
				$this->maxY = max($this->maxY, max($serie));
			}
		}

		foreach ($this->objectives as $objective) {
			$this->minY = min($this->minY, $objective->value);
			$this->maxY = max($this->maxY, $objective->value);
		}

		$this->maxY = $this->maxGridValue(5);
	}

	/** @return float[] */
	public function autoGridLines(int $nlines): array
	{
		return self::computeGridLines($this->minY, $this->maxY, $nlines);
	}

	/**
	 * To compute values of the grid
	 * Logic took from Morrisjs (morris.grid.coffee)
	 *
	 * @return float[]
	 */
	public static function computeGridLines(
		float $ymin,
		float $ymax,
		int $nlines
	): array {
		$span = $ymax - $ymin;
		$ymag = floor(log($span, 10));
		$unit = pow(10, $ymag);

		// calculate initial grid min and max values
		$gmin = floor($ymin / $unit) * $unit;
		$gmax = ceil($ymax / $unit) * $unit;
		$step = ($gmax - $gmin) / ($nlines - 1);
		if ($unit == 1 && $step > 1 && ceil($step) != $step) {
			$step = ceil($step);
			$gmax = $gmin + $step * ($nlines - 1);
		}

		// ensure zero is plotted where the range includes zero
		if ($gmin < 0 && $gmax > 0) {
			$gmin = floor($ymin / $step) * $step;
			$gmax = ceil($ymax / $step) * $step;
		}

		// special case for decimal numbers
		if ($step < 1) {
			$smag = (int) floor(log($step, 10));
			$grid = [];
			for ($y = $gmin; $y <= $gmax; $y += $step) {
				$grid[] = (float) number_format($y, 1 - $smag, '.', '');
			}
		} else {
			$grid = [];
			for ($y = $gmin; $y <= $gmax; $y += $step) {
				$grid[] = $y;
			}
		}

		return $grid;
	}

	/**
	 * To get the value of the highest value of the grid
	 */
	public function maxGridValue(int $nlines): float
	{
		$levels = $this->autoGridLines($nlines);

		return $levels[count($levels) - 1];
	}

	public function convertYValue(int|float $y): float
	{
		return ($y - $this->minY) / ($this->maxY - $this->minY);
	}
}
