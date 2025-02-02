<?php declare(strict_types=1);

namespace Pierresh\Simca\Charts\Axis\XAxis;

use Pierresh\Simca\Charts\Axis\XAxis\LabelPosition;

class XAxisStandard implements XAxisInterface
{
	private float $minX = 0;

	private float $maxX = 100;

	/** @var string[] $labelsDisplayed */
	private array $labelsDisplayed = [];

	/** @param array<string> $labels */
	public function __construct(private readonly array $labels)
	{
		$this->maxX = count($labels) - 1;
	}

	public function getMinX(): float
	{
		return $this->minX;
	}

	public function getMaxX(): float
	{
		return $this->maxX;
	}

	/** @return string[] */
	public function getLabelsDisplayed(): array
	{
		return $this->labelsDisplayed;
	}

	public function getXLabelsPosition(): array
	{
		$xLabels = [];

		foreach ($this->labels as $index => $label) {
			$xLabels[] = new LabelPosition($index, $label);
		}

		return $xLabels;
	}

	public function computeLabelsDisplayed(): void
	{
		$this->labelsDisplayed = $this->labels;
	}

	public function convertXValue(int|float $x): float
	{
		return $x / (count($this->labels) - 1);
	}
}
