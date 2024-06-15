<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Handler;

use Pierresh\Simca\Model\Dot;

class LineChartHandler
{
	/** @param array<Dot> $dots */
	public function createCurvedPath(array $dots): string
	{
		/**@var object{x:float|null, y:float|null} */
		$prevCoord = (object) ['x' => null, 'y' => null];

		$path = '';

		$grads = $this->gradients($dots);

		$xdots = [];

		foreach ($dots as $i => $dot) {
			if (is_null($prevCoord->y)) {
				$prevCoord = $dot;

				$path .= 'M' . $dot->x . ',' . $dot->y;

				continue;
			}

			$g = $grads[$i];
			$lg = $grads[$i - 1];
			$ix = ($dot->x - $prevCoord->x) / 4;

			$x1 = $prevCoord->x + $ix;
			$y1 = $prevCoord->y + $ix * $lg;

			$x = $dot->x;
			$y = $dot->y;

			$x2 = $dot->x - $ix;
			$y2 = $dot->y - $ix * $g;

			// prettier-ignore
			$path .= "C" . $x1 .",".$y1.",".$x2.",".$y2.",".$x.",".$y;
			$xdots[] = (int) $x1 . ' - ' . (int) $x2 . ' - ' . (int) $x;

			$prevCoord = $dot;
		}

		return $path;
	}

	/**
	 * @param array<Dot> $dots
	 * @return array<float|null>
	 */
	private function gradients(array $dots): array
	{
		$grads = [];
		foreach ($dots as $i => $dot) {
			if (is_null($dot->y)) {
				$grads[] = null;
				continue;
			}

			$nextCoord = $dots[$i + 1] ?? new Dot();
			$prevCoord = $dots[$i - 1] ?? new Dot();

			if (!is_null($prevCoord->y) && !is_null($nextCoord->y)) {
				$grads[] = $this->grad($prevCoord, $nextCoord);
			} elseif (!is_null($prevCoord->y)) {
				$grads[] = $this->grad($prevCoord, $dot);
			} elseif (!is_null($nextCoord->y)) {
				$grads[] = $this->grad($dot, $nextCoord);
			} else {
				$grads[] = null;
			}
		}

		return $grads;
	}

	private function grad(Dot $dotA, Dot $dotB): float
	{
		return ($dotA->y - $dotB->y) / ($dotA->x - $dotB->x);
	}

	/**
	 * @param array<Dot> $dots
	 * @return array{startDot: Dot, endDot: Dot}
	 */
	public function computeTrendLine(
		array $dots,
		float $startTrend,
		float $endTrend
	): array {
		// Compute root mean square trend line
		$sumX = 0;
		$sumY = 0;
		$sumXY = 0;
		$sumX2 = 0;
		$sumY2 = 0;
		$n = 0;

		foreach ($dots as $dot) {
			if (is_null($dot->y)) {
				continue;
			}

			$sumX += $dot->x;
			$sumY += $dot->y;
			$sumXY += $dot->x * $dot->y;
			$sumX2 += $dot->x ** 2;
			$sumY2 += $dot->y ** 2;
			$n++;
		}

		$a = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX ** 2);
		$b = ($sumY - $a * $sumX) / $n;

		$startDot = new Dot($startTrend, $a * $startTrend + $b);

		$endDot = new Dot($endTrend, $a * $endTrend + $b);

		return ['startDot' => $startDot, 'endDot' => $endDot];
	}
}
