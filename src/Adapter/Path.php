<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Shapes\SVGPath;

class Path extends SVGNodeContainer
{
	static function build(
		string $path,
		string $color = '#aaaaaa',
		float $width = 0.5
	): SVGNodeContainer {
		$obj = new SVGPath($path);
		$obj->setAttribute('stroke', $color);
		$obj->setAttribute('stroke-width', $width);
		$obj->setAttribute('fill', 'transparent');

		return $obj;
	}

	static function filled(
		string $path,
		string $color = '#aaaaaa',
		float $width = 0.5
	): SVGNodeContainer {
		$obj = self::build($path, $color, $width);
		$obj->setAttribute('fill', $color);

		return $obj;
	}
}
