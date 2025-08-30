<?php

declare(strict_types=1);

namespace Pierresh\Simca\Exception;

class InvalidChartDataException extends ChartException
{
	public static function emptySeries(): self
	{
		return new self('Chart series cannot be empty');
	}

	public static function emptyLabels(): self
	{
		return new self('Chart labels cannot be empty');
	}

	public static function mismatchedSeriesLength(
		int $expectedLength,
		int $actualLength
	): self {
		return new self(
			"Series length mismatch: expected {$expectedLength}, got {$actualLength}"
		);
	}

	public static function invalidSerieData(string $reason): self
	{
		return new self("Invalid serie data: {$reason}");
	}
}
