<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Texts\SVGText;

class Text extends SVGNodeContainer
{
	// Text styling constants
	private const DEFAULT_FONT_SIZE = 12;
	private const TRANSFORM_ORIGIN = '100% 50%';
	private const DEFAULT_TEXT_COLOR = '#888888';

	static function label(
		string $value,
		float $coordX,
		float $coordY,
		int $angle = 0
	): SVGText {
		$value = trim($value);

		$text = new SVGText($value, $coordX, $coordY);
		$text->setAttribute('font-family', 'Sans-serif');
		$text->setStyle('font-size', self::DEFAULT_FONT_SIZE);

		if ($angle === 0) {
			$text->setAttribute('text-anchor', 'middle');
		} else {
			$text->setAttribute('text-anchor', 'end');
			$text->setStyle('transform-box', 'fill-box');
			$text->setStyle('transform-origin', self::TRANSFORM_ORIGIN);
			$text->setStyle('transform', 'rotate(-' . $angle . 'deg)');
		}

		return $text;
	}

	static function labelLeft(
		string $value,
		float $coordX,
		float $coordY
	): SVGNodeContainer {
		$value = trim($value);

		$obj = new SVGText($value, $coordX, $coordY);
		$obj->setAttribute('font-family', 'Sans-serif');
		$obj->setAttribute('text-anchor', 'end');
		$obj->setAttribute('alignment-baseline', 'middle');
		$obj->setAttribute('font-size', self::DEFAULT_FONT_SIZE);
		$obj->setAttribute('fill', self::DEFAULT_TEXT_COLOR);

		return $obj;
	}

	static function labelRight(
		string $value,
		float $coordX,
		float $coordY
	): SVGText {
		$value = trim($value);

		$obj = new SVGText($value, $coordX, $coordY);
		$obj->setAttribute('font-family', 'Sans-serif');
		$obj->setAttribute('text-anchor', 'start');
		$obj->setAttribute('alignment-baseline', 'middle');
		$obj->setAttribute('font-size', self::DEFAULT_FONT_SIZE);
		$obj->setAttribute('fill', self::DEFAULT_TEXT_COLOR);

		return $obj;
	}
}
