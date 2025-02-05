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

	/** @var number[] */
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
		if ($this->fillOpacity > 0) {
			foreach ($this->dots as $indexSerie => $serie) {
				$this->addChartAreaFill($serie, $indexSerie);
			}
		}

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
			$startDot = new Dot(
				$x,
				$this->computeDotY1($this->yAxis1->getMinY())
			);
			$endDot = new Dot(
				$x,
				$this->computeDotY1($this->yAxis1->getMaxY())
			);

			$obj = Line::build($startDot, $endDot, '#273646', 1.5);
			$this->addChild($obj);
		}
	}

	private function drawTrend(): void
	{
		if (!$this->trend) {
			return;
		}

		$startTrend = $this->computeDotX($this->xAxis->getMinX(), 0);
		$endTrend = $this->computeDotX($this->xAxis->getMaxX(), 0);

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

	/** @param Dot[] $dots */
	private function straigthLine(array $dots, int $i): void
	{
		$color = $this->getColor($i);

		$path = $this->handler->createStraightPath($dots);

		$obj = Path::build($path, $color, 3);

		$this->addChild($obj);
	}

	/** @param Dot[] $dots */
	private function curvedLine(array $dots, int $index): void
	{
		$path = $this->handler->createCurvedPath($dots);

		$color = $this->getColor($index);

		$obj = Path::build($path, $color, 3);

		$this->addChild($obj);
	}

	/** @param Dot[] $dots */
	private function addChartAreaFill(array $dots, int $index): void
	{
		if ($this->fillOpacity === 0) {
			return;
		}

		$color = $this->getColor($index);

		if ($this->lineType === 'straight') {
			$pathFill = $this->handler->createStraightPath($dots);
		} else {
			$pathFill = $this->handler->createCurvedPath($dots);
		}

		if ($index === 0) {
			$dot1 = new Dot(
				$this->computeDotX($this->xAxis->getMaxX()),
				$this->computeDotY1($this->yAxis1->getMinY())
			);
			$dot2 = new Dot(
				$this->computeDotX($this->xAxis->getMinX()),
				$this->computeDotY1($this->yAxis1->getMinY())
			);

			$pathFill .= ' L ' . $dot1->x . ' ' . $dot1->y;
			$pathFill .= ' L ' . $dot2->x . ' ' . $dot2->y;
		} else {
			$previousSerie = $this->dots[$index - 1];
			$lastpoint = $previousSerie[count($previousSerie) - 1];

			$pathFill .= ' L ' . $lastpoint->x . ' ' . $lastpoint->y;

			if ($this->lineType === 'straight') {
				$previousPath = $this->handler->createStraightPath(
					array_reverse($this->dots[$index - 1])
				);
			} else {
				$previousPath = $this->handler->createCurvedPath(
					array_reverse($this->dots[$index - 1])
				);
			}

			$pathFill .= ' ' . $previousPath;

			$pathFill .= ' L ' . $dots[0]->x . ' ' . $dots[0]->y;
		}

		$objFill = Path::filled($pathFill, $color, 0);
		$objFill->setAttribute('fill-opacity', $this->fillOpacity);
		$this->addChild($objFill);
	}

	/** @param Dot[] $dots */
	private function addCircle(array $dots, int $index): void
	{
		foreach ($dots as $dot) {
			$color = $this->getColor($index);

			$circle = Circle::build($dot, $color);

			$this->addChild($circle);
		}
	}

	/** @param Dot[] $dots */
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
}
