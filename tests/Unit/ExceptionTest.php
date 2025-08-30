<?php

use Pierresh\Simca\Charts\BarChart;
use Pierresh\Simca\Exception\InvalidChartDataException;
use Pierresh\Simca\Exception\InvalidChartOptionsException;
use Pierresh\Simca\Exception\ChartConfigurationException;

test('throws exception for empty labels', function () {
	$chart = new BarChart();

	expect(fn() => $chart->setLabels([]))->toThrow(
		InvalidChartDataException::class,
		'Chart labels cannot be empty'
	);
});

test('throws exception for invalid numLines option', function () {
	$chart = new BarChart();

	expect(fn() => $chart->setOptions(['numLines' => -1]))->toThrow(
		InvalidChartOptionsException::class,
		'Number of grid lines must be positive, got: -1'
	);
});

test('throws exception for invalid fillOpacity option', function () {
	$chart = new BarChart();

	expect(fn() => $chart->setOptions(['fillOpacity' => 1.5]))->toThrow(
		InvalidChartOptionsException::class,
		'Fill opacity must be between 0 and 1, got: 1.5'
	);
});

test('throws exception for invalid labelAngle option', function () {
	$chart = new BarChart();

	expect(fn() => $chart->setOptions(['labelAngle' => 180]))->toThrow(
		InvalidChartOptionsException::class,
		'Label angle must be between -90 and 90 degrees, got: 180'
	);
});

test('throws exception for too many Y2 keys', function () {
	$chart = new BarChart();
	$chart->setLabels(['A', 'B', 'C']);
	$chart->setSeries([[1, 2, 3]]);
	$chart->setOptions(['nbYkeys2' => 2]);

	expect(fn() => $chart->render())->toThrow(
		ChartConfigurationException::class,
		'Number of Y2 keys (2) cannot exceed total series count (1)'
	);
});

test('throws exception for mismatched series length', function () {
	$chart = new BarChart();
	$chart->setLabels(['A', 'B', 'C']);
	$chart->setSeries([[1, 2], [3, 4, 5]]);

	expect(fn() => $chart->render())->toThrow(
		InvalidChartDataException::class,
		'Series length mismatch: expected 3, got 2'
	);
});
