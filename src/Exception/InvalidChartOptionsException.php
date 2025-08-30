<?php

declare(strict_types=1);

namespace Pierresh\Simca\Exception;

class InvalidChartOptionsException extends ChartException
{
	public static function invalidNumLines(int $value): self
	{
		return new self("Number of grid lines must be positive, got: {$value}");
	}

	public static function invalidFillOpacity(float $value): self
	{
		return new self("Fill opacity must be between 0 and 1, got: {$value}");
	}

	public static function invalidLabelAngle(int $value): self
	{
		return new self(
			"Label angle must be between -90 and 90 degrees, got: {$value}"
		);
	}

	public static function invalidNbYkeys2(int $value): self
	{
		return new self(
			"Number of Y2 keys must be non-negative, got: {$value}"
		);
	}

	public static function invalidMargin(int $value): self
	{
		return new self("Margin must be non-negative, got: {$value}");
	}
}
