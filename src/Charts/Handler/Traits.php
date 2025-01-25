<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Handler;

use SVG\Nodes\SVGNode;

/**
 * @phpstan-import-type Serie from \Pierresh\Simca\Charts\AbstractChart
 */
trait Traits
{
	/** @param array<Serie> $series */
	public function setSeries(array $series): self
	{
		$this->series = $series;

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

	protected function getColor(int $index): string
	{
		$colorIndex = $index % count($this->colors);

		return $this->colors[$colorIndex];
	}

	protected function addChild(SVGNode $child): void
	{
		$this->chart->addChild($child);
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
		return '<img src="data:image/svg+xml;base64,' . base64_encode((string) $svg) . '"/>';
	}
}
