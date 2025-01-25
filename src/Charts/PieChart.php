<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts;

use SVG\Nodes\Structures\SVGDocumentFragment;

use Pierresh\Simca\Adapter\SVG;
use Pierresh\Simca\Adapter\Path;
use Pierresh\Simca\Adapter\Line;
use Pierresh\Simca\Adapter\Text;
use Pierresh\Simca\Charts\PieChartSlice;
use Pierresh\Simca\Charts\Handler\Traits;
use Pierresh\Simca\Model\Dot;

/**
 * @phpstan-type Serie array<int, float>
 */
class PieChart
{
	use Traits;

	/** @var array<Serie> */
	protected array $series = [];

	/** @var array<string> */
	protected array $colors = [
		'#3B91C3',
		'#6BC6B6',
		'#F4D384',
		'#F09596',
		'#FBB77B',
		'#9E96D7',
	];

	private float $sum = 0;

	private float $gap = 3;

	private float $radius = 1;

	protected bool $responsive = true;

	protected SVGDocumentFragment $chart;

	public function __construct(
		protected readonly int $width = 500,
		protected readonly int $height = 400
	) {}

	/**
	 * @param array{ responsive?: bool } $options
	 */
	public function setOptions(array $options): self
	{
		if (isset($options['responsive'])) {
			$this->responsive = $options['responsive'];
		}

		return $this;
	}

	/** Render the chart as a pure SVG */
	public function generateChart(): string
	{
		if ($this->responsive) {
			$image = SVG::buildResponsive($this->width, $this->height);
		} else {
			$image = SVG::build($this->width, $this->height);
		}

		$this->chart = $image->getDocument();

		$this->computeSum();

		$this->computeRadius();

		$this->drawSlices();

		$this->addGaps();

		$this->addLabels();

		return $image->toXMLString();
	}

	private function drawSlices(): void
	{
		$center = $this->getCenter();

		$startAngle = 0;

		foreach ($this->series as $indexSerie => $serie) {
			$endAngle = $startAngle + (360 * $serie[0]) / $this->sum;

			$cRadius = $this->getPolarRadius($serie);

			$svg = (new PieChartSlice(
				$center,
				$cRadius,
				$startAngle,
				$endAngle
			))->getPath();

			$borderWidth = 0;

			if ((int) $this->gap === 0) {
				$borderWidth = 1;
			}

			$path = Path::filled(
				$svg,
				$this->getColor($indexSerie),
				$borderWidth
			);

			$this->addChild($path);

			$startAngle = $endAngle;
		}
	}

	private function addGaps(): void
	{
		$startAngle = 0;

		$center = $this->getCenter();

		foreach ($this->series as $serie) {
			$endAngle = $startAngle + (360 * $serie[0]) / $this->sum;

			$endPoint = new Dot(
				$center->x + cos(deg2rad($startAngle)) * $this->radius,
				$center->y + sin(deg2rad($startAngle)) * $this->radius
			);

			$path = Line::build($center, $endPoint, '#ffffff', $this->gap);

			$this->addChild($path);

			$startAngle = $endAngle;
		}
	}

	private function addLabels(): void
	{
		$startAngle = 0;

		$center = $this->getCenter();

		foreach ($this->series as $serie) {
			$endAngle = $startAngle + (360 * $serie[0]) / $this->sum;

			$angle = $startAngle + ($endAngle - $startAngle) / 2;

			$radius = $this->getPolarRadius($serie) + 10;

			$posX = $center->x + cos(deg2rad($angle)) * $radius;
			$posY = $center->y + sin(deg2rad($angle)) * $radius + 5;

			$obj = Text::label((string) $serie[0], $posX, $posY);

			$this->addChild($obj);

			$startAngle = $endAngle;
		}
	}

	private function computeSum(): void
	{
		$this->sum = 0;
		foreach ($this->series as $serie) {
			$this->sum += $serie[0];
		}
	}

	private function getCenter(): Dot
	{
		return new Dot($this->width / 2, $this->height / 2);
	}

	private function computeRadius(): void
	{
		$this->radius = min($this->width - 40, $this->height - 40) / 2;
	}

	/** @param Serie $serie */
	private function getPolarRadius(array $serie): float
	{
		if (isset($serie[1])) {
			return $this->radius * $serie[1];
		}

		return $this->radius;
	}
}
