<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Helper;

use Exception;
use NumberFormatter;

class Helper
{
	/**
	 * Number formatter for data labels
	 * Took from Laravel Illuminate\Support\Number
	 */
	public static function format(
		int|float $number,
		?int $precision = null,
		?int $maxPrecision = null
	): string {
		$formatter = new NumberFormatter('en', NumberFormatter::DECIMAL);

		if (!is_null($maxPrecision)) {
			$formatter->setAttribute(
				NumberFormatter::MAX_FRACTION_DIGITS,
				$maxPrecision
			);
		} elseif (!is_null($precision)) {
			$formatter->setAttribute(
				NumberFormatter::FRACTION_DIGITS,
				$precision
			);
		}

		$formatted = $formatter->format($number);

		if (!$formatted) {
			return (string) $number;
		}

		return (string) $formatted;
	}

	public static function convertLabelToTimestamp(string $label): int
	{
		$ts = strtotime($label);

		if (!$ts) {
			throw new Exception($label . ' cannot be converted to a timestamp');
		}

		return $ts;
	}

	/**
	 * Determines whether the specified hexadecimal is color dark.
	 * This is used for the datalabel in stacked bar charts
	 * To know if the label should be displayed in black or white
	 */
	public static function isColorDark(string $hex): bool
	{
		if ($hex === '' || $hex === '0') {
			return false;
		}

		$hex = substr($hex, 1);
		$rgb = intval($hex, 16);
		$r = ($rgb >> 16) & 0xff;
		$g = ($rgb >> 8) & 0xff;
		$b = $rgb & 0xff;
		$luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

		return $luma < 128;
	}

	/**
	 * Helper function to prevent Rector removing option type casting,
	 * While still keep PHPDocs of the public function setOptions()
	 *
	 * @param array<string,string|int|float|bool> $options
	 * @return array<string,string|int|float|bool> $options
	 */
	public static function convertOptions(array $options): array {
		return $options;
	}
}
