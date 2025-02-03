<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use SVG\Nodes\Structures\SVGDocumentFragment;

use Pierresh\Simca\Adapter\SVG;
use Pierresh\Simca\Adapter\Circle;
use Pierresh\Simca\Adapter\Line;
use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Adapter\Path;
use Pierresh\Simca\Charts\Axis\YAxis\YAxisStandard;
use Pierresh\Simca\Charts\Handler\Traits;
use Pierresh\Simca\Charts\Helper\Helper;
use Pierresh\Simca\Model\Dot;

/**
 * @phpstan-type Serie number[]
 */
class RadarChart
{
	use Traits;

	/** @var Serie[] */
	protected array $series = [];

	/** @var string[] */
	protected array $labels = [];

	/** @var string[] */
	protected array $colors = [
		'#3B91C3',
		'#6BC6B6',
		'#F4D384',
		'#F09596',
		'#FBB77B',
		'#9E96D7',
	];

	private float $angle = 1;

	private float $nbAxis = 1;

	private float $radius = 1;

	private float $min = 0;

	private float $max = 0;

	private float $fillOpacity = 0.25;

	private float $startAngle = 270;

	private bool $stacked = false;

	protected int $numLines = 5;

	/** @var float[] */
	private array $levels = [];

	protected bool $responsive = true;

	/** @var Dot[][] */
	protected array $dots;

	/**
	 * Buffer to compute stacked values
	 * @var number[]
	 */
	private array $tmpStacked = [];

	private Dot $center;

	protected SVGDocumentFragment $chart;

	public function __construct(
		protected readonly int $width = 500,
		protected readonly int $height = 400
	) {}

	public function generateChart(): string
	{
		if ($this->responsive) {
			$image = SVG::buildResponsive($this->width, $this->height);
		} else {
			$image = SVG::build($this->width, $this->height);
		}

		$this->chart = $image->getDocument();

		$this->computeAngle();

		$this->computeMax();

		$this->setCenter();

		$this->computeRadius();

		$this->drawGrid();

		$this->drawGridLines();

		$this->computeDots();

		$this->drawAreas();

		$this->drawSeries();

		$this->addLabels();

		return $image->toXMLString();
	}

	/**
	 * @param array{ fillOpacity?: float, stacked?: bool, responsive?: bool } $options
	 */
	public function setOptions(array $options): self
	{
		$options = Helper::convertOptions($options);

		if (isset($options['fillOpacity'])) {
			$this->fillOpacity = (float) $options['fillOpacity'];
		}

		if (isset($options['stacked'])) {
			$this->stacked = (bool) $options['stacked'];
		}

		if (isset($options['responsive'])) {
			$this->responsive = (bool) $options['responsive'];
		}

		return $this;
	}

	private function computeDots(): void
	{
		$this->dots = [];

		foreach ($this->series as $serie) {
			$this->addComputedSerie($serie);
		}
	}

	private function computeDot(
		float $angle,
		float $value,
		float $offset = 0
	): Dot {
		// prettier-ignore
		return new Dot(
			$this->center->x + cos(deg2rad($angle)) * ($this->axisPosition($value) + $offset),
			$this->center->y + sin(deg2rad($angle)) * ($this->axisPosition($value) + $offset),
			$value
		);
	}

	/** @param Serie $serie */
	private function addComputedSerie($serie): void
	{
		$currentAngle = $this->startAngle;

		$computedSerie = [];

		foreach ($serie as $value) {
			if ($this->stacked) {
				$value += $this->tmpStacked[$currentAngle] ?? 0;
				$this->tmpStacked[$currentAngle] = $value;
			}

			$dot = $this->computeDot($currentAngle, $value);

			$computedSerie[] = $dot;

			$currentAngle += $this->angle;
		}

		$this->dots[] = $computedSerie;
	}

	private function drawAreas(): void
	{
		if ($this->stacked) {
			foreach (
				array_reverse($this->dots, true)
				as $indexDotSerie => $dotSerie
			) {
				$this->drawArea($indexDotSerie, $dotSerie);
			}
		} else {
			foreach ($this->dots as $indexDotSerie => $dotSerie) {
				$this->drawArea($indexDotSerie, $dotSerie);
			}
		}
	}

	private function drawSeries(): void
	{
		if ($this->stacked) {
			foreach (
				array_reverse($this->dots, true)
				as $indexDotSerie => $dotSerie
			) {
				$this->drawSerie($indexDotSerie, $dotSerie);
			}
		} else {
			foreach ($this->dots as $indexDotSerie => $dotSerie) {
				$this->drawSerie($indexDotSerie, $dotSerie);
			}
		}

		foreach ($this->dots as $indexDotSerie => $dotSerie) {
			$this->addCircle($indexDotSerie, $dotSerie);
		}
	}

	/** @param Dot[] $dotSerie */
	private function addCircle(int $indexDotSerie, array $dotSerie): void
	{
		foreach ($dotSerie as $dot) {
			$color = $this->getColor($indexDotSerie);

			$circle = Circle::build($dot, $color, 2.5);

			$this->addChild($circle);
		}
	}

	/** @param Dot[] $dotSerie */
	private function drawArea(int $indexDotSerie, array $dotSerie): void
	{
		$svg = $this->computeRadarPath($dotSerie);

		$path = Path::filled($svg, $this->getColor($indexDotSerie), 0);

		$path->setAttribute('fill-opacity', $this->fillOpacity);

		$this->addChild($path);
	}

	/** @param Dot[] $dotSerie */
	private function drawSerie(int $indexDotSerie, array $dotSerie): void
	{
		$svg = $this->computeRadarPath($dotSerie);

		$path = Path::build($svg, $this->getColor($indexDotSerie), 2);

		$this->addChild($path);
	}

	/** @param Dot[] $dotSerie */
	private function computeRadarPath(array $dotSerie): string
	{
		$d = [];

		foreach ($dotSerie as $indexDot => $dot) {
			if ($indexDot === 0) {
				$d = array_merge($d, ['M', $dot->x, $dot->y]);
				continue;
			}

			$d = array_merge($d, ['L', $dot->x, $dot->y]);
		}

		$d = array_merge($d, ['L', $dotSerie[0]->x, $dotSerie[0]->y]);

		return implode(' ', $d);
	}

	/** @param string[] $labels */
	public function setLabels(array $labels): self
	{
		$this->labels = $labels;

		return $this;
	}

	private function addLabels(): void
	{
		$currentAngle = $this->startAngle;

		// prettier-ignore
		foreach ($this->labels as $label) {
			$offset = 5;
			if ((int) $currentAngle === (int) $this->startAngle) {
				$offset = 15;
			}

			$dot = $this->computeDot($currentAngle, $this->max, $offset);

			$obj = Text::label($label, (float) $dot->x, (float) $dot->y);
			$obj->setAttribute('fill', '#666666');

			if ((int) $currentAngle === (int) $this->startAngle) {
				$obj->setStyle('text-anchor', 'middle');
			} elseif ($dot->x < $this->center->x) {
				$obj->setStyle('text-anchor', 'end');
			} elseif ($dot->x > $this->center->x) {
				$obj->setStyle('text-anchor', 'start');
			}

			$this->addChild($obj);

			$currentAngle += $this->angle;
		}
	}

	private function drawGridLines(): void
	{
		foreach ($this->levels as $line) {
			$this->drawGridSegment($line);
		}
	}

	private function drawGridSegment(float $value): void
	{
		$currentAngle = $this->startAngle;

		for ($i = 0; $i < $this->nbAxis; $i++) {
			$startDot = $this->computeDot($currentAngle, $value);

			$currentAngle += $this->angle;

			$endDot = $this->computeDot($currentAngle, $value);

			$path = Line::build($startDot, $endDot, '#e5e5e5', 1);

			$this->addChild($path);

			if ($i === 0) {
				// prettier-ignore
				$obj = Text::label((string) $value, (float) $startDot->x, (float) $startDot->y);
				$obj->setAttribute('fill', '#666666');
				$this->addChild($obj);
			}
		}
	}

	private function axisPosition(float $value): float
	{
		return ($value * $this->radius) / ($this->max - $this->min);
	}

	private function drawGrid(): void
	{
		$currentAngle = $this->startAngle;

		for ($i = 0; $i < $this->nbAxis; $i++) {
			$endPoint = $this->computeDot($currentAngle, $this->max);

			$path = Line::build($this->center, $endPoint, '#dddddd', 1.5);

			$this->addChild($path);

			$currentAngle += $this->angle;
		}
	}

	private function setCenter(): void
	{
		$this->center = new Dot($this->width / 2, $this->height / 2);
	}

	private function computeRadius(): void
	{
		$this->radius = min($this->width - 50, $this->height - 50) / 2;
	}

	private function computeAngle(): void
	{
		$this->nbAxis = count($this->series[0]);
		$this->angle = 360 / $this->nbAxis;
	}

	private function computeMax(): void
	{
		if ($this->stacked) {
			// Sum series
			$sum = [];
			foreach ($this->labels as $index => $label) {
				$sum[$index] = 0;
				foreach ($this->series as $serie) {
					$sum[$index] += $serie[$index];
				}
			}

			if ($sum === []) {
				return;
			}

			$this->max = max($sum);
		} else {
			foreach ($this->series as $serie) {
				if ($serie === []) {
					return;
				}

				$this->max = max($this->max, max($serie));
			}
		}

		$this->levels = YAxisStandard::computeGridLines(
			$this->min,
			$this->max,
			$this->numLines
		);

		$this->max = $this->levels[count($this->levels) - 1];
	}
}
