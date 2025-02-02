<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Axis\XAxis;

interface XAxisInterface
{
	public function getMinX(): float;

	public function getMaxX(): float;

	/** @return string[] */
	public function getLabelsDisplayed(): array;

	/** @return LabelPosition[] */
	public function getXLabelsPosition(): array;

	public function computeLabelsDisplayed(): void;

	public function convertXValue(int|float $x): float;
}
