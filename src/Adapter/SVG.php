<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use SVG\SVG as PhpSvg;

class SVG
{
	static function build(int $width, int $height): PhpSvg
	{
		return new PhpSvg($width, $height);
	}
}
