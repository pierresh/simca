<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

class Grid
{
	/**
	 * To compute values of the grid
	 * Logic took from Morrisjs (morris.grid.coffee)
	 *
	 * @return array<float>
	 */
	public function autoGridLines(float $ymin, float $ymax, int $nlines): array
	{
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
	public function maxGridValue(float $ymin, float $ymax, int $nlines): float
	{
		$levels = $this->autoGridLines($ymin, $ymax, $nlines);

		return $levels[count($levels) - 1];
	}
}
