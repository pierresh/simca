<?php

declare(strict_types=1);

namespace Pierresh\Simca\Charts\Helper;

class PathBuilder
{
	/** @var string[] $commands */
	private array $commands = [];

	public static function create(): self
	{
		return new self();
	}

	public function moveTo(float $x, float $y): self
	{
		$this->commands[] = 'M' . $x . ',' . $y;
		return $this;
	}

	public function lineTo(float $x, float $y): self
	{
		$this->commands[] = 'L' . $x . ',' . $y;
		return $this;
	}

	public function horizontalTo(float $x): self
	{
		$this->commands[] = 'H' . $x;
		return $this;
	}

	public function verticalTo(float $y): self
	{
		$this->commands[] = 'V' . $y;
		return $this;
	}

	public function closePath(): self
	{
		$this->commands[] = 'Z';
		return $this;
	}

	public function curveTo(
		float $x1,
		float $y1,
		float $x2,
		float $y2,
		float $x,
		float $y
	): self {
		$this->commands[] =
			'C' . $x1 . ',' . $y1 . ' ' . $x2 . ',' . $y2 . ' ' . $x . ',' . $y;
		return $this;
	}

	public function quadraticTo(float $x1, float $y1, float $x, float $y): self
	{
		$this->commands[] = 'Q' . $x1 . ',' . $y1 . ' ' . $x . ',' . $y;
		return $this;
	}

	public function build(): string
	{
		return implode('', $this->commands);
	}
}
