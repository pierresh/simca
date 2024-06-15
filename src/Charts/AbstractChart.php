<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use SVG\Nodes\SVGNode;
use SVG\Nodes\Structures\SVGDocumentFragment;

use Pierresh\Simca\Model\Dot;
use Pierresh\Simca\Charts\Grid;
use Pierresh\Simca\Model\Objective;
use Pierresh\Simca\Adapter\SVG;
use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Adapter\Path;
use Pierresh\Simca\Charts\Helper\Helper;

/**
 * @phpstan-type Serie array<int, float>
 */
abstract class AbstractChart
{
	/** @var array<Serie> */
	protected array $series = [];

	/** @var array<string> */
	protected array $labels = [];

	/** @var array<Objective> */
	protected array $objectivesY1 = [];

	/** @var array<Objective> */
	protected array $objectivesY2 = [];

	protected bool $isTimeChart = false;

	protected int $nbYkeys2 = 0;

	protected int $numLines = 5;

	protected bool $showYAxis = false;

	protected bool $stacked = false;

	protected string $unitY1 = '';

	protected string $unitY2 = '';

	/** @var array<string> */
	protected array $colors = [
		'#3B91C3',
		'#6BC6B6',
		'#F4D384',
		'#F09596',
		'#FBB77B',
		'#9E96D7',
	];

	protected SVGDocumentFragment $chart;

	protected Grid $grid;

	/** Distance around the chart */
	protected int $padding = 20;

	/** Distance to display labels on Yaxis */
	protected int $paddingLabel = 40;

	/** Distance between axis and first/last value */
	protected int $marginChart = 60;

	protected float $minX = 0;

	protected float $maxX = 100;

	protected float $minY1 = 0;

	protected float $maxY1 = 100;

	protected float $minY2;

	protected float $maxY2;

	/** @var array<array<Dot>> */
	protected array $dots;

	protected string $timeFormat = 'Y-m-d H:i';

	/**
	 * Buffer to compute stacked values on axis Y1
	 * @var array<int, float>
	 */
	private array $tmpStackedY1 = [];

	/**
	 * Buffer to compute stacked values on axis Y2
	 * @var array<int, float>
	 */
	private array $tmpStackedY2 = [];

	public function __construct(
		protected readonly int $width = 500,
		protected readonly int $height = 400
	) {
		$this->grid = new Grid();
	}

	/** @param array<Serie> $series */
	public function setSeries(array $series): self
	{
		$this->series = $series;

		return $this;
	}

	/** @param array<string> $labels */
	public function setLabels(array $labels): self
	{
		$this->labels = $labels;

		return $this;
	}

	/** @param array<string> $colors */
	public function setColors(array $colors): self
	{
		if ($colors === []) {
			return $this;
		}

		$this->colors = $colors;

		return $this;
	}

	/**
	 * Public method for acceptance tests
	 *
	 * @return array<array<Dot>>
	 */
	public function getDots(): array
	{
		$this->generateChart();

		return $this->dots;
	}

	/** Render the chart as a pure SVG */
	public function render(): string
	{
		return $this->generateChart();
	}

	/** Render the chart as a base64 encoded SVG image */
	public function renderBase64(): string
	{
		$svg = $this->generateChart();

		// prettier-ignore
		return '<img src="data:image/svg+xml;base64,' . base64_encode($svg) . '"/>';
	}

	/**
	 * @param array{
	 *  numLines?: int,
	 *  stacked?: bool,
	 *  timeChart?: bool,
	 *  nbYkeys2?: int,
	 *  showYAxis?: bool,
	 *  unitY1?: string,
	 *  unitY2?: string,
	 * } $options
	 */
	public function setOptions(array $options): self
	{
		$options = Helper::convertOptions($options);

		if (isset($options['numLines'])) {
			$this->numLines = (int) $options['numLines'];
		}

		if (isset($options['stacked'])) {
			$this->stacked = (bool) $options['stacked'];
		}

		if (isset($options['timeChart'])) {
			$this->isTimeChart = (bool) $options['timeChart'];
		}

		if (isset($options['margin'])) {
			$this->marginChart = (int) $options['margin'];
		}

		if (isset($options['showYAxis'])) {
			$this->showYAxis = (bool) $options['showYAxis'];
		}

		if (isset($options['nbYkeys2'])) {
			$this->nbYkeys2 = (int) $options['nbYkeys2'];
		}

		if (isset($options['unitY1'])) {
			$this->unitY1 = (string) $options['unitY1'];
		}

		if (isset($options['unitY2'])) {
			$this->unitY2 = (string) $options['unitY2'];
		}

		return $this;
	}

	private function generateChart(): string
	{
		$image = SVG::build($this->width, $this->height);
		$this->chart = $image->getDocument();

		$this->computeMinMax();

		if ($this->showYAxis) {
			$this->drawYaxis();
		}

		$this->drawXaxis();

		$this->computeDots();

		$this->drawChart();

		$this->drawObjectives();

		$this->addXAxisLabels();

		return $image->toXMLString();
	}

	abstract protected function drawChart(): void;

	abstract protected function computeDotXNum(int $x): float;

	abstract protected function computeMinMaxXaxis(): void;

	private function computeMinMax(): void
	{
		$this->minY1 = 0;
		$this->maxY1 = 0.1;

		$this->minY2 = 0;
		$this->maxY2 = 0.1;

		if ($this->stacked) {
			foreach ($this->labels as $indexLabel => $label) {
				$tmpCumulY1 = 0;
				$tmpCumulY2 = 0;
				foreach ($this->series as $indexSerie => $serie) {
					if ($this->leftAxis($indexSerie)) {
						$tmpCumulY1 += $serie[$indexLabel];
					} else {
						$tmpCumulY2 += $serie[$indexLabel];
					}
				}

				$this->maxY1 = max($this->maxY1, $tmpCumulY1);
				$this->maxY2 = max($this->maxY2, $tmpCumulY2);
			}
		} else {
			foreach ($this->series as $indexSerie => $serie) {
				if ($this->leftAxis($indexSerie)) {
					$this->minY1 = min($this->minY1, min($serie));
					$this->maxY1 = max($this->maxY1, max($serie));
				} else {
					$this->minY2 = min($this->minY2, min($serie));
					$this->maxY2 = max($this->maxY2, max($serie));
				}
			}
		}

		foreach ($this->objectivesY1 as $objective) {
			$this->minY1 = min($this->minY1, $objective->value);
			$this->maxY1 = max($this->maxY1, $objective->value);
		}

		foreach ($this->objectivesY2 as $objective) {
			$this->minY2 = min($this->minY2, $objective->value);
			$this->maxY2 = max($this->maxY2, $objective->value);
		}

		$this->maxY1 = $this->grid->maxGridValue(
			$this->minY1,
			$this->maxY1,
			$this->numLines
		);

		if ($this->has2Yaxis()) {
			$this->maxY2 = $this->grid->maxGridValue(
				$this->minY2,
				$this->maxY2,
				$this->numLines
			);
		}

		if ($this->isTimeChart) {
			$this->computeMinMaxXaxis();
		}

		$this->adjustPaddingLabel();
	}

	protected function adjustPaddingLabel(): void
	{
		$labelMaxY1 = Helper::format($this->maxY1) . ' ' . $this->unitY1;
		$lengthLabelMaxY1 = strlen($labelMaxY1) * 6;

		if ($this->paddingLabel < $lengthLabelMaxY1) {
			$this->paddingLabel = $lengthLabelMaxY1;
		}

		if ($this->has1Yaxis()) {
			return;
		}

		$labelMaxY2 = Helper::format($this->maxY2) . ' ' . $this->unitY2;
		$lengthLabelMaxY2 = strlen($labelMaxY2) * 6;

		if ($this->paddingLabel < $lengthLabelMaxY2) {
			$this->paddingLabel = $lengthLabelMaxY2;
		}
	}

	private function drawYaxis(): void
	{
		$start = $this->computeDotY1($this->minY1);

		$end = $this->computeDotY1($this->maxY1);

		$vertical = $this->computeDotX($this->minX);

		$path = 'M' . $vertical . '.5,' . $start . 'V' . $end;

		$obj = Path::build($path);

		$this->addChild($obj);
	}

	private function drawXaxis(): void
	{
		$start = $this->computeDotX($this->minX, 0);

		$end = $this->computeDotX($this->maxX, 0);

		$levels = $this->grid->autoGridLines(
			$this->minY1,
			$this->maxY1,
			$this->numLines
		);

		foreach ($levels as $level) {
			$this->addYaxis1Label($level);
			$horizontal = $this->computeDotY1($level);

			$path = 'M' . $start . ',' . $horizontal . '.5H' . $end;

			$obj = Path::build($path);

			$this->chart->addChild($obj);
		}

		if ($this->has1Yaxis()) {
			return;
		}

		$this->displayYaxis2Labels();
	}

	private function displayYaxis2Labels(): void
	{
		$levels2 = $this->grid->autoGridLines(
			$this->minY2,
			$this->maxY2,
			$this->numLines
		);

		foreach ($levels2 as $level) {
			$this->addYaxis2Label($level);
		}
	}

	private function drawObjectives(): void
	{
		foreach ($this->objectivesY1 as $objective) {
			$level = $this->computeDotY1($objective->value);

			$this->drawObjective($objective, $level);

			$this->addObjectiveYaxis1Label($objective, $level);
		}

		foreach ($this->objectivesY2 as $objective) {
			$level = $this->computeDotY2($objective->value);

			$this->drawObjective($objective, $level);

			$this->addObjectiveYaxis2Label($objective, $level);
		}
	}

	private function addObjectiveYaxis1Label(
		Objective $objective,
		float $level
	): void {
		$x = $this->computeDotX($this->minX, 0);
		$text = Text::labelRight((string) $objective->value, $x, $level - 8);
		$text->setAttribute('fill', $objective->color);
		$this->chart->addChild($text);
	}

	private function addObjectiveYaxis2Label(
		Objective $objective,
		float $level
	): void {
		$x = $this->computeDotX($this->maxX);
		$text = Text::labelLeft((string) $objective->value, $x, $level - 8);
		$text->setAttribute('fill', $objective->color);
		$this->chart->addChild($text);
	}

	private function drawObjective(Objective $objective, float $level): void
	{
		// prettier-ignore
		$path = 'M' . $this->computeDotX($this->minX, 0) . ',' . $level . '.5H' . $this->computeDotX($this->maxX, 0);

		$obj = Path::build($path, $objective->color, $objective->width);

		$this->chart->addChild($obj);
	}

	protected function addYaxis1Label(float $value): void
	{
		$coordY = $this->computeDotY1($value);
		$coordX = $this->paddingLabel;

		$value = Helper::format($value) . ' ' . $this->unitY1;

		$text = Text::labelLeft($value, $coordX, $coordY);
		$this->chart->addChild($text);
	}

	protected function addYaxis2Label(float $value): void
	{
		$coordY = $this->computeDotY2($value);
		$coordX = $this->width - $this->paddingLabel;

		$value = Helper::format($value) . ' ' . $this->unitY2;

		$text = Text::labelRight($value, $coordX, $coordY);
		$this->chart->addChild($text);
	}

	private function addXAxisLabels(): void
	{
		if ($this->isTimeChart) {
			$this->addXAxisLabelsTime();
			return;
		}

		foreach ($this->labels as $index => $label) {
			$x = $this->computeDotXNum($index);

			$this->addXAxisLabel($label, $x);
		}
	}

	private function addXAxisLabelsTime(): void
	{
		$this->timeFormat = $this->guessTimeFormat();

		for ($i = 0; $i < 5; $i++) {
			$ts = $this->getTimeStampStep($i);
			$label = date($this->timeFormat, $ts);
			$x = $this->computeDotX($ts);

			$this->addXAxisLabel($label, $x);
		}
	}

	private function addXAxisLabel(string $label, float $x): void
	{
		$text = Text::label($label, $x, $this->height - 2);
		$text->setAttribute('fill', '#888888');

		$this->addChild($text);
	}

	protected function computeDotX(float $x, float $margin = null): float
	{
		if (is_null($margin)) {
			$margin = $this->marginChart;
		}

		$paddingLabel = $this->paddingLabel;
		if ($this->has2Yaxis()) {
			$paddingLabel = 2 * $this->paddingLabel;
		}

		// prettier-ignore
		$width = $this->width - 2 * $this->padding - $paddingLabel - $margin * 2;

		// prettier-ignore
		$x = $this->padding + $this->paddingLabel + $margin + ($width * ($x - $this->minX)) / ($this->maxX - $this->minX);

		return round($x, 2);
	}

	protected function computeDotY1(float $y): float
	{
		$height = $this->height - 2 * $this->padding;

		// prettier-ignore
		$y1 = $this->height - $this->padding - ($height * ($y - $this->minY1)) / ($this->maxY1 - $this->minY1);

		return round($y1, 2);
	}

	protected function computeDotY2(float $y): float
	{
		$height = $this->height - 2 * $this->padding;

		// prettier-ignore
		$y2 = $this->height - $this->padding - ($height * ($y - $this->minY2)) / ($this->maxY2 - $this->minY2);

		return round($y2, 2);
	}

	private function computeDots(): void
	{
		$this->dots = [];

		foreach ($this->series as $indexSerie => $serie) {
			$this->addComputedSerie($serie, $indexSerie);
		}
	}

	/** @param Serie $serie */
	private function addComputedSerie($serie, int $indexSerie): void
	{
		$computedSerie = [];

		foreach ($serie as $index => $value) {
			$displayedValue = $value;

			if ($this->stacked) {
				if ($this->leftAxis($indexSerie)) {
					if (!isset($this->tmpStackedY1[$index])) {
						$this->tmpStackedY1[$index] = 0;
					}

					$this->tmpStackedY1[$index] += $value;

					$value = $this->tmpStackedY1[$index];
				} else {
					if (!isset($this->tmpStackedY2[$index])) {
						$this->tmpStackedY2[$index] = 0;
					}

					$this->tmpStackedY2[$index] += $value;

					$value = $this->tmpStackedY2[$index];
				}
			}

			if ($this->leftAxis($indexSerie)) {
				$val = $this->computeDotY1($value);
			} else {
				$val = $this->computeDotY2($value);
			}

			if ($this->isTimeChart) {
				$t = Helper::convertLabelToTimestamp($this->labels[$index]);
				$x = $this->computeDotX($t);
			} else {
				$x = $this->computeDotXNum($index);
			}

			$dot = new Dot($x, $val, $displayedValue);

			$computedSerie[] = $dot;
		}

		$this->dots[] = $computedSerie;
	}

	public function addObjectiveY1(
		float $value,
		string $color = 'red',
		float $width = 1
	): AbstractChart {
		$obj = new Objective($value, $color, $width);

		$this->objectivesY1[] = $obj;

		return $this;
	}

	public function addObjectiveY2(
		float $value,
		string $color = 'red',
		float $width = 1
	): AbstractChart {
		$obj = new Objective($value, $color, $width);

		$this->objectivesY2[] = $obj;

		return $this;
	}

	protected function leftAxis(int $indexSerie): bool
	{
		return $indexSerie < count($this->series) - $this->nbYkeys2;
	}

	protected function has1Yaxis(): bool
	{
		return $this->nbYkeys2 === 0;
	}

	protected function has2Yaxis(): bool
	{
		return $this->nbYkeys2 > 0;
	}

	protected function getColor(int $index): string
	{
		$colorIndex = $index % count($this->colors);

		return $this->colors[$colorIndex];
	}

	private function getTimeStampStep(int $index): int
	{
		$duration = $this->maxX - $this->minX;

		$step = $duration / count($this->labels);

		$total = $this->minX + $index * $step;

		return (int) $total;
	}

	/**
	 * Try to guess the most suitable datetime format
	 * depending on the time range of the chart
	 */
	private function guessTimeFormat(): string
	{
		$diff = $this->maxX - $this->minX;

		if ($diff < 24 * 60 * 60) {
			// Less than 24 hours, display H:i (hours and minutes)
			return 'H:i';
		} elseif ($diff < 7 * 24 * 60 * 60) {
			// Less than a week, display "day H:i"
			return 'D H:i';
		} elseif ($diff < 90 * 24 * 60 * 60) {
			// Less than 3 months, display "YYYY-MM-DD H:i"
			return 'Y-m-d H:i';
		} elseif ($diff < 365 * 24 * 60 * 60) {
			// Less than a year, display "YYYY-MM-DD"
			return 'Y-m-d';
		} else {
			// More than a year, display "YYYY"
			return 'Y';
		}
	}

	protected function addChild(SVGNode $child): void
	{
		$this->chart->addChild($child);
	}
}
