<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Adapter\Circle;
use Pierresh\Simca\Adapter\Path;
use Pierresh\Simca\Adapter\Line;
use Pierresh\Simca\Charts\AbstractChart;
use Pierresh\Simca\Charts\Handler\LineChartHandler;
use Pierresh\Simca\Charts\Helper\Helper;
use Pierresh\Simca\Model\Dot;

/**
 * @phpstan-import-type Serie from \Pierresh\Simca\Charts\AbstractChart
 */
class LineChart extends AbstractChart
{
	/** @var 'curved' | 'straight' */
	private string $lineType = 'curved';

	private bool $trend = false;

	private readonly LineChartHandler $handler;

	/** @var array<float> */
	private array $events = [];

	public function __construct(int $width = 500, int $height = 400)
	{
		parent::__construct($width, $height);

		$this->handler = new LineChartHandler();
	}

	/** @param 'curved' | 'straight' $lineType */
	public function setLineType(string $lineType): self
	{
		$this->lineType = $lineType;

		return $this;
	}

	public function showTrend(): self
	{
		$this->trend = true;

		return $this;
	}

	public function drawChart(): void
	{
		foreach ($this->dots as $indexSerie => $serie) {
			if ($this->lineType === 'straight') {
				$this->straigthLine($serie, $indexSerie);
			} else {
				$this->curvedLine($serie, $indexSerie);
			}

			$this->addCircle($serie, $indexSerie);
		}

		foreach ($this->dots as $serie) {
			$this->addLabels($serie);
		}

		$this->drawTrend();

		$this->drawEvents();
	}

	private function drawEvents(): void
	{
		foreach ($this->events as $event) {
			$x = $this->computeDotX($event);
			$startDot = new Dot($x, $this->computeDotY1($this->minY1));
			$endDot = new Dot($x, $this->computeDotY1($this->maxY1));

			$obj = Line::build($startDot, $endDot, '#273646', 1.5);
			$this->addChild($obj);
		}
	}

	private function drawTrend(): void
	{
		if (!$this->trend) {
			return;
		}

		$startTrend = $this->computeDotX($this->minX, 0);
		$endTrend = $this->computeDotX($this->maxX, 0);

		foreach ($this->dots as $indexSerie => $serie) {
			$trend = $this->handler->computeTrendLine(
				$serie,
				$startTrend,
				$endTrend
			);

			$color = $this->getColor($indexSerie);

			$obj = Line::build($trend['startDot'], $trend['endDot'], $color, 1);

			$this->addChild($obj);
		}
	}

	public function addEvent(string $datetime): self
	{
		$this->events[] = Helper::convertLabelToTimestamp($datetime);

		return $this;
	}

	/** @param array<Dot> $dots */
	private function straigthLine(array $dots, int $i): void
	{
		$startDot = new Dot();

		foreach ($dots as $dot) {
			if (is_null($startDot->y)) {
				$startDot = $dot;
				continue;
			}

			$endDot = $dot;

			$color = $this->getColor($i);

			$obj = Line::build($startDot, $endDot, $color);

			$this->addChild($obj);

			$startDot = $dot;
		}
	}

	/** @param array<Dot> $dots */
	private function curvedLine(array $dots, int $index): void
	{
		$path = $this->handler->createCurvedPath($dots);

		$color = $this->getColor($index);

		$obj = Path::build($path, $color, 3);

		$this->addChild($obj);
	}

	/** @param array<Dot> $dots */
	private function addCircle(array $dots, int $index): void
	{
		foreach ($dots as $dot) {
			$color = $this->getColor($index);

			$circle = Circle::build($dot, $color);

			$this->addChild($circle);
		}
	}

	/** @param array<Dot> $dots */
	private function addLabels(array $dots): void
	{
		foreach ($dots as $dot) {
			if (is_null($dot->value) || is_null($dot->x)) {
				continue;
			}

			$text = Helper::format($dot->value);

			$obj = Text::label($text, $dot->x, $dot->y - 8);

			$this->addChild($obj);
		}
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

	protected function computeMinMaxXaxis(): void
	{
		$minX = null;
		$maxX = null;

		foreach ($this->labels as $label) {
			$timestamp = Helper::convertLabelToTimestamp($label);

			if (is_null($minX)) {
				$minX = $timestamp;
			}

			if (is_null($maxX)) {
				$maxX = $timestamp;
			}

			if ($minX > $timestamp) {
				$minX = $timestamp;
			}

			if ($maxX < $timestamp) {
				$maxX = $timestamp;
			}
		}

		$this->minX = (float) $minX;
		$this->maxX = (float) $maxX;
	}
}
