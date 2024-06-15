<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use Pierresh\Simca\Model\Dot;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Shapes\SVGLine;

class Line extends SVGNodeContainer
{
	static function build(
		Dot $start,
		Dot $end,
		string $color = 'blue',
		float $width = 3,
	): SVGNodeContainer {
		$obj = new SVGLine($start->x, $start->y, $end->x, $end->y);
		$obj->setAttribute('stroke', $color);
		$obj->setAttribute('stroke-width', $width);

		return $obj;
	}
}
