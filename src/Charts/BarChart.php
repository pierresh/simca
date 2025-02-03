<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Adapter\Rect;
use Pierresh\Simca\Charts\AbstractChart;
use Pierresh\Simca\Charts\Helper\Helper;
use Pierresh\Simca\Model\Dot;

/**
 * @phpstan-import-type Serie from \Pierresh\Simca\Charts\AbstractChart
 */
class BarChart extends AbstractChart
{
	private float $barGap = 3;

	private float $barSizeRatio = 0.75;

	private float $groupWidth = 0;

	private float $barWidth = 0;

	public function __construct(int $width = 500, int $height = 400)
	{
		parent::__construct($width, $height);
	}

	public function drawChart(): void
	{
		$this->computeGroupWidth();

		foreach ($this->dots as $index => $serie) {
			$this->drawBars($serie, $index);
		}

		foreach ($this->dots as $index => $serie) {
			$this->addLabels($serie, $index);
		}
	}

	protected function computeDotX(float $x, float $margin = null): float
	{
		$this->computeGroupWidth();

		// prettier-ignore
		return $this->padding + $this->paddingLabel + $x * $this->groupWidth + $this->groupWidth / 2;
	}

	private function computeGroupWidth(): void
	{
		$paddingLabel = $this->paddingLabel;
		if ($this->has2Yaxis()) {
			$paddingLabel = 2 * $this->paddingLabel;
		}

		$width = $this->width - 2 * $this->padding - $paddingLabel;
		$this->groupWidth = $width / count($this->labels);

		if (!$this->stacked) {
			$nbColumns = count($this->series);
		} elseif ($this->has2Yaxis()) {
			$nbColumns = 2;
		} else {
			$nbColumns = 1;
		}

		// prettier-ignore
		$this->barWidth = ($this->barSizeRatio * $this->groupWidth) / $nbColumns - $this->barGap;
	}

	protected function computeMinMaxXaxis(): void {}

	/** @param Dot[] $dots */
	private function drawBars(array $dots, int $indexSerie): void
	{
		$val = $this->computeDotY1(0);

		$color = $this->getColor($indexSerie);

		foreach ($dots as $dot) {
			if (is_null($dot->y) || is_null($dot->value)) {
				continue;
			}

			$height = $val - $dot->y;

			// prettier-ignore
			if (!$this->stacked) {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2 + $this->barWidth * $indexSerie;

				$pointX += $indexSerie * $this->barGap;
			} elseif ($this->has1Yaxis()) {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2;

				$height = $val - $this->computeDotY1($dot->value);
			} elseif ($this->leftAxis($indexSerie)) {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2;

				$height = $val - $this->computeDotY1($dot->value);
			} else {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2 + $this->barWidth + $this->barGap;
			}

			// prettier-ignore
			$obj = Rect::build($pointX, $dot->y, $this->barWidth, $height, $color);

			$this->addChild($obj);
		}
	}

	/** @param Dot[] $dots */
	private function addLabels(array $dots, int $indexSerie): void
	{
		foreach ($dots as $dot) {
			if (is_null($dot->x) || is_null($dot->value)) {
				continue;
			}

			// prettier-ignore
			if (!$this->stacked) {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2 + $this->barWidth * $indexSerie + $this->barWidth / 2;
				$pointX += $indexSerie  * $this->barGap;

				$pointY = $dot->y - 8;
			} elseif ($this->has1Yaxis()) {
				$pointX = $dot->x;

				$pointY = $dot->y + ($this->height - $this->computeDotY1($dot->value)) / 2 - 4;
			} elseif ($this->leftAxis($indexSerie)) {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2 + $this->barWidth / 2;

				$pointY = $dot->y + ($this->height - $this->computeDotY1($dot->value)) / 2 - 4;
			} else {
				$pointX = $dot->x - ($this->barSizeRatio * $this->groupWidth) / 2 + $this->barWidth + $this->barWidth / 2;

				$pointY = $dot->y + ($this->height - $this->computeDotY2($dot->value)) / 2 - 4;
			}

			if ($this->stacked) {
				$pointY -= $this->paddingLabelX / 2;
			}

			$obj = Text::label((string) $dot->value, $pointX, $pointY);
			$color = $this->getColor($indexSerie);

			if ($this->stacked && Helper::isColorDark($color)) {
				$obj->setAttribute('fill', 'white');
			}

			$this->addChild($obj);
		}
	}
}
