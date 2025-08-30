<?php

declare(strict_types=1);

namespace Pierresh\Simca\Exception;

class ChartConfigurationException extends ChartException
{
	public static function tooManyYkeys2(int $nbYkeys2, int $totalSeries): self
	{
		return new self(
			"Number of Y2 keys ({$nbYkeys2}) cannot exceed total series count ({$totalSeries})"
		);
	}

	public static function incompatibleStackedAndDualAxis(): self
	{
		return new self(
			'Stacked charts with dual Y-axis are not fully supported'
		);
	}

	public static function missingRequiredData(string $field): self
	{
		return new self("Missing required chart data: {$field}");
	}
}
