<?php declare(strict_types=1);

namespace Pierresh\Simca\Charts\Axis\XAxis;

use Pierresh\Simca\Charts\Helper\Helper;
use Pierresh\Simca\Charts\Axis\XAxis\LabelPosition;

class XAxisTime implements XAxisInterface
{
	private float $minX = 0;

	private float $maxX = 100;

	/**
	 * Store labels diplayed on X axis - because it might be reformatted for time series
	 * @var string[] $labelsDisplayed
	 */
	public array $labelsDisplayed = [];

	private string $timeFormat = 'Y-m-d H:i';

	/** @param string[] $labels */
	public function __construct(private readonly array $labels)
	{
		$this->computeMinMaxXaxis();
		$this->guessTimeFormat();
	}

	public function getMinX(): float
	{
		return $this->minX;
	}

	public function getMaxX(): float
	{
		return $this->maxX;
	}

	/** @return string[] */
	public function getLabelsDisplayed(): array
	{
		return $this->labelsDisplayed;
	}

	/** @return LabelPosition[] */
	public function getXLabelsPosition(): array
	{
		$xLabels = [];

		for ($i = 0; $i < 5; $i++) {
			$ts = $this->getTimeStampStep($i);
			$label = date($this->timeFormat, $ts);

			$xLabels[] = new LabelPosition($ts, $label);
		}

		return $xLabels;
	}

	public function computeLabelsDisplayed(): void
	{
		for ($i = 0; $i < 5; $i++) {
			$ts = $this->getTimeStampStep($i);
			$label = date($this->timeFormat, $ts);

			$this->labelsDisplayed[] = $label;
		}
	}

	private function computeMinMaxXaxis(): void
	{
		$minX = null;
		$maxX = null;

		foreach ($this->labels as $label) {
			$timestamp = Helper::convertLabelToTimestamp($label);

			if (is_null($minX)) {
				$minX = $timestamp;
			}

			if (is_null($maxX)) {
				$maxX = $timestamp;
			}

			if ($minX > $timestamp) {
				$minX = $timestamp;
			}

			if ($maxX < $timestamp) {
				$maxX = $timestamp;
			}
		}

		$this->minX = (float) $minX;
		$this->maxX = (float) $maxX;
	}

	/**
	 * Try to guess the most suitable datetime format
	 * depending on the time range of the chart
	 */
	private function guessTimeFormat(): void
	{
		$diff = $this->maxX - $this->minX;

		if ($diff < 24 * 60 * 60) {
			// Less than 24 hours, display H:i (hours and minutes)
			$this->timeFormat = 'H:i';
		} elseif ($diff < 7 * 24 * 60 * 60) {
			// Less than a week, display "day H:i"
			$this->timeFormat = 'D H:i';
		} elseif ($diff < 90 * 24 * 60 * 60) {
			// Less than 3 months, display "YYYY-MM-DD H:i"
			$this->timeFormat = 'Y-m-d H:i';
		} elseif ($diff < 365 * 24 * 60 * 60) {
			// Less than a year, display "YYYY-MM-DD"
			$this->timeFormat = 'Y-m-d';
		} else {
			// More than a year, display "YYYY"
			$this->timeFormat = 'Y';
		}
	}

	public function convertXValue(int|float $x): float
	{
		return ($x - $this->minX) / ($this->maxX - $this->minX);
	}

	private function getTimeStampStep(int $index): int
	{
		$duration = $this->getMaxX() - $this->getMinX();

		$step = $duration / count($this->labels);

		$total = $this->getMinX() + $index * $step;

		return (int) $total;
	}
}
