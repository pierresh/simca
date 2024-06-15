<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Texts\SVGText;

class Text extends SVGNodeContainer
{

	static function label(string $value, float $coordX, float $coordY): SVGText
	{
		$value = trim($value);

		$text = new SVGText($value, $coordX, $coordY);
		$text->setAttribute('font-family', 'Sans-serif');
		$text->setStyle('text-anchor', 'middle');
		$text->setStyle('font-size', 12);

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
		$obj->setAttribute('font-size', 12);
		$obj->setAttribute('fill', '#888888');

		return $obj;
	}

	static function labelRight(string $value, float $coordX, float $coordY): SVGText
	{
		$value = trim($value);

		$obj = new SVGText($value, $coordX, $coordY);
		$obj->setAttribute('font-family', 'Sans-serif');
		$obj->setAttribute('text-anchor', 'start');
		$obj->setAttribute('alignment-baseline', 'middle');
		$obj->setAttribute('font-size', 12);
		$obj->setAttribute('fill', '#888888');

		return $obj;
	}
}
