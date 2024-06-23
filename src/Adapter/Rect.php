<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Shapes\SVGRect;

class Rect extends SVGNodeContainer
{
	static function build(
		float $x,
		float $y,
		float $width,
		float $height,
		string $color
	): SVGNodeContainer {
		$obj = new SVGRect($x, $y, $width, $height);
		$obj->setStyle('stroke', $color);
		$obj->setStyle('fill', $color);
		$obj->setStyle('stroke-width', 1);

		return $obj;
	}
}
