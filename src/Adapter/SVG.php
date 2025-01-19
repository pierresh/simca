<?php

declare(strict_types=1);

namespace Pierresh\Simca\Adapter;

use SVG\SVG as PhpSvg;

class SVG
{
	static function build(int $width, int $height): PhpSvg
	{
		$svg = new PhpSvg();

		$svg->getDocument()
			->setAttribute('viewBox', '0 0 ' . $width . ' ' . $height)
			->setAttribute('preserveAspectRatio', 'xMidYMid meet'); // equivalent of `object-fit: contain`

		return $svg;
	}
}
