<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Adapter\Circle;
use Pierresh\Simca\Charts\AbstractChart;
use Pierresh\Simca\Model\Dot;
use Pierresh\Simca\Charts\Helper\Helper;

/**
 * @phpstan-import-type Serie from \Pierresh\Simca\Charts\AbstractChart
 */
class BubbleChart extends AbstractChart
{
	public array $labels;

	public function __construct(int $width = 500, int $height = 400)
	{
		parent::__construct($width, $height);
	}

	public function drawChart(): void
	{
		$this->computeMinMax();

		$this->drawBubbles();
	}

	protected function computeLabels(): void
	{
		$this->labels = [];
		foreach ($this->series as $serie) {
			$this->labels[] = (string) $serie[0];
		}
	}

	private function drawBubbles(): void
	{
		foreach ($this->series as $indexSerie => $serie) {
			if ($this->isTimeChart) {
				$ts = (float) Helper::convertLabelToTimestamp(
					(string) $serie[0]
				);
			} else {
				$ts = $serie[0];
			}

			$x = $this->computeDotX($ts);
			$y = $this->computeDotY1($serie[1]);
			$value = $serie[2];
			$dot = new Dot($x, $y, $value);

			$color = $this->getColor($indexSerie);

			$circle = Circle::build($dot, $color, (float) $dot->value);

			$this->addChild($circle);

			$this->displayValue($dot, $color);
		}
	}

	private function displayValue(Dot $dot, string $color): void
	{
		// prettier-ignore
		$obj = Text::label((string) $dot->value, (float) $dot->x, (float) $dot->y);

		$obj->setAttribute('alignment-baseline', 'middle');

		if ($this->stacked && Helper::isColorDark($color)) {
			$obj->setAttribute('fill', 'white');
		}

		$this->addChild($obj);
	}

	protected function computeMinMax(): void
	{
		$this->minY1 = 0;
		$this->maxY1 = 0;

		foreach ($this->series as $serie) {
			$this->minY1 = min($this->minY1, $serie[1] + 10);
			$this->maxY1 = max($this->maxY1, $serie[1] + 10);
		}

		$this->adjustPaddingXLabel();
	}

	protected function computeDotXNum(int $x): float
	{
		$paddingLabel = $this->paddingLabel;
		if ($this->has2Yaxis()) {
			$paddingLabel = 2 * $this->paddingLabel;
		}

		// prettier-ignore
		$width = $this->width - 2 * $this->padding - $paddingLabel - $this->marginChart * 2;

		// prettier-ignore
		$x = $this->padding + $this->paddingLabel + $this->marginChart + ($width * $x) / (count($this->labels) - 1);

		return round($x, 2);
	}
}
