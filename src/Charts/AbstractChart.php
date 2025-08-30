<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use SVG\Nodes\Structures\SVGDocumentFragment;

use Pierresh\Simca\Model\Dot;
use Pierresh\Simca\Model\Objective;
use Pierresh\Simca\Adapter\SVG;
use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Adapter\Path;
use Pierresh\Simca\Charts\BubbleChart;
use Pierresh\Simca\Charts\Axis\XAxis\XAxisInterface;
use Pierresh\Simca\Charts\Axis\XAxis\XAxisStandard;
use Pierresh\Simca\Charts\Axis\XAxis\XAxisTime;
use Pierresh\Simca\Charts\Axis\YAxis\YAxisInterface;
use Pierresh\Simca\Charts\Axis\YAxis\YAxisStandard;
use Pierresh\Simca\Charts\Helper\Helper;
use Pierresh\Simca\Charts\Handler\Traits;

/**
 * @phpstan-type Serie (int|float)[]
 */
abstract class AbstractChart
{
	use Traits;

	// Chart dimension constants
	private const DEFAULT_WIDTH = 500;
	private const DEFAULT_HEIGHT = 400;

	// Layout constants
	private const CHART_PADDING = 20;
	private const Y_AXIS_LABEL_PADDING = 40;
	private const AXIS_MARGIN = 60;
	private const DEFAULT_GRID_LINES = 5;

	// Label calculation constants
	private const CHAR_WIDTH_MULTIPLIER = 6;
	private const LABEL_LENGTH_MULTIPLIER = 7;

	// Text color constants
	private const DEFAULT_TEXT_COLOR = '#888888';

	// Objective label offset
	private const OBJECTIVE_LABEL_OFFSET = 8;

	/** @var Serie[] */
	protected array $series = [];

	/** @var string[] */
	protected array $labels = [];

	/** @var Objective[] */
	protected array $objectivesY1 = [];

	/** @var Objective[] */
	protected array $objectivesY2 = [];

	protected bool $isTimeChart = false;

	protected int $nbYkeys2 = 0;

	protected int $numLines = self::DEFAULT_GRID_LINES;

	protected bool $showYAxis = false;

	protected bool $stacked = false;

	protected string $unitY1 = '';

	protected string $unitY2 = '';

	protected bool $responsive = true;

	/** @var string[] */
	protected array $colors = [
		'#3B91C3',
		'#6BC6B6',
		'#F4D384',
		'#F09596',
		'#FBB77B',
		'#9E96D7',
	];

	protected SVGDocumentFragment $chart;

	/** Distance around the chart */
	protected int $padding = self::CHART_PADDING;

	/** Distance to display labels on Yaxis */
	protected int $paddingLabel = self::Y_AXIS_LABEL_PADDING;

	/** Distance to display labels on Xaxis */
	protected int $paddingLabelX = 0;

	/** Angle of Xaxis labels in degrees */
	protected int $labelAngle = 0;

	/** Distance between axis and first/last value */
	protected int $marginChart = self::AXIS_MARGIN;

	protected int|float $fillOpacity = 0;

	protected YAxisInterface $yAxis1;

	protected YAxisInterface $yAxis2;

	/** @var Dot[][] */
	protected array $dots;

	protected string $timeFormat = 'Y-m-d H:i';

	/**
	 * Buffer to compute stacked values on axis Y1
	 * @var (int|float)[]
	 */
	private array $tmpStackedY1 = [];

	/**
	 * Buffer to compute stacked values on axis Y2
	 * @var (int|float)[]
	 */
	private array $tmpStackedY2 = [];

	protected XAxisInterface $xAxis;

	public function __construct(
		protected readonly int $width = self::DEFAULT_WIDTH,
		protected readonly int $height = self::DEFAULT_HEIGHT
	) {}

	/** @param string[] $labels */
	public function setLabels(array $labels): self
	{
		$this->labels = $labels;

		return $this;
	}

	/**
	 * Public method for acceptance tests
	 *
	 * @return Dot[][]
	 */
	public function getDots(): array
	{
		$this->generateChart();

		return $this->dots;
	}

	/**
	 * @param array{
	 *  numLines?: int,
	 *  stacked?: bool,
	 *  timeChart?: bool,
	 *  nbYkeys2?: int,
	 *  showYAxis?: bool,
	 *  fillOpacity?: float,
	 *  unitY1?: string,
	 *  unitY2?: string,
	 *  labelAngle?: int,
	 *  responsive?: bool,
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

		if (isset($options['fillOpacity'])) {
			$this->fillOpacity = (float) $options['fillOpacity'];
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

		if (isset($options['labelAngle'])) {
			$this->labelAngle = (int) $options['labelAngle'];
		}

		if (isset($options['responsive'])) {
			$this->responsive = (bool) $options['responsive'];
		}

		return $this;
	}

	protected function generateChart(): string
	{
		if ($this->isBubbleChart()) {
			$this->computeLabels();
		}

		if ($this->isTimeChart) {
			$this->xAxis = new XAxisTime($this->labels);
		} else {
			$this->xAxis = new XAxisStandard($this->labels);
		}

		if ($this->nbYkeys2 === 0) {
			$seriesAxis1 = $this->series;
		} else {
			$seriesAxis1 = array_slice($this->series, 0, -$this->nbYkeys2);
		}

		$this->yAxis1 = new YAxisStandard(
			$this->labels,
			$seriesAxis1,
			$this->objectivesY1,
			$this->stacked
		);

		if ($this->nbYkeys2 > 0) {
			$seriesAxis2 = array_slice($this->series, -$this->nbYkeys2);

			$this->yAxis2 = new YAxisStandard(
				$this->labels,
				$seriesAxis2,
				$this->objectivesY2,
				$this->stacked
			);
		}

		if ($this->responsive) {
			$image = SVG::buildResponsive($this->width, $this->height);
		} else {
			$image = SVG::build($this->width, $this->height);
		}

		$this->chart = $image->getDocument();

		$this->computeMinMax();

		$this->drawYaxis();

		$this->drawXaxis();

		if (!$this->isBubbleChart()) {
			$this->computeDots();
		}

		$this->drawChart();

		$this->drawObjectives();

		if (!$this->isBubbleChart() || $this->isTimeChart) {
			$this->addXAxisLabels();
		}

		return $image->toXMLString();
	}

	abstract protected function drawChart(): void;

	protected function computeLabels(): void {}

	protected function computeMinMax(): void
	{
		$this->adjustPaddingXLabel();
		$this->adjustPaddingLabel();
	}

	protected function adjustPaddingLabel(): void
	{
		// prettier-ignore
		$labelMaxY1 = Helper::format($this->yAxis1->getMaxY()) . ' ' . $this->yAxis1->getUnit();
		$lengthLabelMaxY1 = strlen($labelMaxY1) * self::CHAR_WIDTH_MULTIPLIER;

		if ($this->paddingLabel < $lengthLabelMaxY1) {
			$this->paddingLabel = $lengthLabelMaxY1;
		}

		if ($this->has1Yaxis()) {
			return;
		}

		// prettier-ignore
		$labelMaxY2 = Helper::format($this->yAxis2->getMaxY()) . ' ' . $this->yAxis2->getUnit();
		$lengthLabelMaxY2 = strlen($labelMaxY2) * self::CHAR_WIDTH_MULTIPLIER;

		if ($this->paddingLabel < $lengthLabelMaxY2) {
			$this->paddingLabel = $lengthLabelMaxY2;
		}
	}

	protected function adjustPaddingXLabel(): void
	{
		$this->xAxis->computeLabelsDisplayed();

		if ($this->xAxis->getLabelsDisplayed() === []) {
			return;
		}

		$maxLabelLength = max(
			array_map(
				fn(string $label): int => strlen($label),
				$this->xAxis->getLabelsDisplayed()
			)
		);

		$this->paddingLabelX =
			(int) (sin(deg2rad($this->labelAngle)) *
				$maxLabelLength *
				self::LABEL_LENGTH_MULTIPLIER);
	}

	private function drawYaxis(): void
	{
		$this->yAxis1->setUnit($this->unitY1);

		if ($this->nbYkeys2 > 0) {
			$this->yAxis2->setUnit($this->unitY2);
		}

		if (!$this->showYAxis) {
			return;
		}

		$start = $this->computeDotY1($this->yAxis1->getMinY());

		$end = $this->computeDotY1($this->yAxis1->getMaxY());

		$vertical = $this->computeDotX($this->xAxis->getMinX());

		$path = 'M' . $vertical . '.5,' . $start . 'V' . $end;

		$obj = Path::build($path);

		$this->addChild($obj);
	}

	private function drawXaxis(): void
	{
		$start = $this->computeDotX($this->xAxis->getMinX(), 0);

		$end = $this->computeDotX($this->xAxis->getMaxX(), 0);

		$levels = $this->yAxis1->autoGridLines($this->numLines);

		foreach ($levels as $level) {
			$this->addYaxis1Label($level);
			$horizontal = (int) $this->computeDotY1($level);

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
		$levels2 = $this->yAxis2->autoGridLines($this->numLines);

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
		$x = $this->computeDotX($this->xAxis->getMinX(), 0);
		$text = Text::labelRight(
			(string) $objective->value,
			$x,
			$level - self::OBJECTIVE_LABEL_OFFSET
		);
		$text->setAttribute('fill', $objective->color);
		$this->chart->addChild($text);
	}

	private function addObjectiveYaxis2Label(
		Objective $objective,
		float $level
	): void {
		$x = $this->computeDotX($this->xAxis->getMaxX());
		$text = Text::labelLeft(
			(string) $objective->value,
			$x,
			$level - self::OBJECTIVE_LABEL_OFFSET
		);
		$text->setAttribute('fill', $objective->color);
		$this->chart->addChild($text);
	}

	private function drawObjective(Objective $objective, float $level): void
	{
		// This prevents conflict in SVG with decimal values
		$level = (int) $level;

		// prettier-ignore
		$path = 'M' . $this->computeDotX($this->xAxis->getMinX(), 0) . ',' . $level . '.5H' . $this->computeDotX($this->xAxis->getMaxX(), 0);

		$obj = Path::build($path, $objective->color, $objective->width);

		$this->chart->addChild($obj);
	}

	protected function addYaxis1Label(float $value): void
	{
		$coordY = $this->computeDotY1($value);
		$coordX = $this->paddingLabel;

		$value = Helper::format($value) . ' ' . $this->yAxis1->getUnit();

		$text = Text::labelLeft($value, $coordX, $coordY);
		$this->chart->addChild($text);
	}

	protected function addYaxis2Label(float $value): void
	{
		$coordY = $this->computeDotY2($value);
		$coordX = $this->width - $this->paddingLabel;

		$value = Helper::format($value) . ' ' . $this->yAxis2->getUnit();

		$text = Text::labelRight($value, $coordX, $coordY);
		$this->chart->addChild($text);
	}

	private function addXAxisLabels(): void
	{
		$xLabels = $this->xAxis->getXLabelsPosition();

		foreach ($xLabels as $xLabel) {
			$x = $this->computeDotX($xLabel->index);
			$this->addXAxisLabel($xLabel->label, $x);
		}
	}

	protected function addXAxisLabel(string $label, float $x): void
	{
		$text = Text::label(
			$label,
			$x,
			$this->height - $this->paddingLabelX - 2,
			$this->labelAngle
		);
		$text->setAttribute('fill', self::DEFAULT_TEXT_COLOR);

		$this->addChild($text);
	}

	protected function computeDotX(float $x, float $margin = null): float
	{
		$margin ??= $this->marginChart;
		$paddingLabel = $this->getEffectivePaddingLabel();

		$width =
			$this->width - 2 * $this->padding - $paddingLabel - $margin * 2;
		$result =
			$this->padding +
			$this->paddingLabel +
			$margin +
			$width * $this->xAxis->convertXValue($x);

		return round($result, 2);
	}

	protected function computeDotY1(float $y): float
	{
		return $this->computeDotY($y, $this->yAxis1);
	}

	protected function computeDotY2(float $y): float
	{
		return $this->computeDotY($y, $this->yAxis2);
	}

	private function computeDotY(float $y, YAxisInterface $yAxis): float
	{
		$chartHeight = $this->getEffectiveChartHeight();
		$height = $chartHeight - 2 * $this->padding;

		$result =
			$chartHeight - $this->padding - $height * $yAxis->convertYValue($y);

		return round($result, 2);
	}

	protected function getEffectivePaddingLabel(): float
	{
		return $this->has2Yaxis()
			? 2 * $this->paddingLabel
			: $this->paddingLabel;
	}

	protected function getEffectiveChartHeight(): float
	{
		return $this->height - $this->paddingLabelX;
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
				$x = $this->computeDotX($index);
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

	private function isBubbleChart(): bool
	{
		return $this instanceof BubbleChart;
	}
}
