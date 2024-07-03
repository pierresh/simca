<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use Pierresh\Simca\Model\Dot;
use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Shapes\SVGCircle;

class Circle extends SVGNodeContainer
{
	static function build(
		Dot $coord,
		string $color = '#0b62a4',
		float $radius = 4,
	): SVGNodeContainer {
		$obj = new SVGCircle($coord->x, $coord->y, $radius);
		$obj->setStyle('stroke', 'white');
		$obj->setStyle('fill', $color);
		$obj->setStyle('stroke-width', 1);

		return $obj;
	}
}
