<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Helper;

use Exception;

class Helper
{
	// Color processing constants
	private const LUMA_RED_COEFFICIENT = 0.2126;
	private const LUMA_GREEN_COEFFICIENT = 0.7152;
	private const LUMA_BLUE_COEFFICIENT = 0.0722;
	private const DARK_COLOR_THRESHOLD = 128;

	/**
	 * Number formatter for data labels
	 * inspired from Laravel Illuminate\Support\Number but without requiring intl extension
	 */
	public static function format(
		int|float $number,
		?int $precision = null,
		?int $maxPrecision = null
	): string {
		if (!is_null($maxPrecision)) {
			$decimals = $maxPrecision;
		} elseif (!is_null($precision)) {
			$decimals = $precision;
		} else {
			// Default: 2 decimal places for floats, 0 for integers
			$decimals = is_float($number) && $number !== floor($number) ? 2 : 0;
		}

		$formatted = number_format($number, $decimals, '.', ',');

		// If using maxPrecision, remove trailing zeros
		if (!is_null($maxPrecision)) {
			$formatted = rtrim($formatted, '0');
			$formatted = rtrim($formatted, '.');
		}

		return $formatted;
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
		$luma =
			self::LUMA_RED_COEFFICIENT * $r +
			self::LUMA_GREEN_COEFFICIENT * $g +
			self::LUMA_BLUE_COEFFICIENT * $b;

		return $luma < self::DARK_COLOR_THRESHOLD;
	}

	/**
	 * Helper function to prevent Rector removing option type casting,
	 * While still keep PHPDocs of the public function setOptions()
	 *
	 * @param array<string,string|int|float|bool> $options
	 * @return array<string,string|int|float|bool> $options
	 */
	public static function convertOptions(array $options): array
	{
		return $options;
	}
}
